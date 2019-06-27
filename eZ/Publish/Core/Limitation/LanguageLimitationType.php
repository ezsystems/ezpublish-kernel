<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Limitation;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation as APILanguageLimitation;
use eZ\Publish\API\Repository\Values\User\UserReference as APIUserReference;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Limitation\Target;
use eZ\Publish\SPI\Limitation\TargetAwareType as SPITargetAwareLimitationType;
use eZ\Publish\SPI\Persistence\Content\Handler as SPIPersistenceContentHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as SPIPersistenceLanguageHandler;
use eZ\Publish\SPI\Persistence\Content\VersionInfo as SPIVersionInfo;

/**
 * LanguageLimitation is a Content limitation.
 */
class LanguageLimitationType implements SPITargetAwareLimitationType
{
    /** @var \eZ\Publish\SPI\Persistence\Content\Language\Handler */
    private $persistenceLanguageHandler;

    /** @var \eZ\Publish\SPI\Persistence\Content\Handler */
    private $persistenceContentHandler;

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $persistenceLanguageHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $persistenceContentHandler
     */
    public function __construct(
        SPIPersistenceLanguageHandler $persistenceLanguageHandler,
        SPIPersistenceContentHandler $persistenceContentHandler
    ) {
        $this->persistenceLanguageHandler = $persistenceLanguageHandler;
        $this->persistenceContentHandler = $persistenceContentHandler;
    }

    /**
     * Accepts a Limitation value and checks for structural validity.
     *
     * Makes sure LimitationValue object and ->limitationValues is of correct type.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitationValue
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected type/structure
     */
    public function acceptValue(APILimitationValue $limitationValue): void
    {
        if (!$limitationValue instanceof APILanguageLimitation) {
            throw new InvalidArgumentType(
                '$limitationValue',
                APILanguageLimitation::class,
                $limitationValue
            );
        } elseif (!is_array($limitationValue->limitationValues)) {
            throw new InvalidArgumentType(
                '$limitationValue->limitationValues',
                'array',
                $limitationValue->limitationValues
            );
        }

        foreach ($limitationValue->limitationValues as $key => $value) {
            if (!is_string($value)) {
                throw new InvalidArgumentType(
                    "\$limitationValue->limitationValues[{$key}]",
                    'string',
                    $value
                );
            }
        }
    }

    /**
     * Makes sure every language code defined as limitation exists.
     *
     * Make sure {@link acceptValue()} is checked first!
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitationValue
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(APILimitationValue $limitationValue): array
    {
        $validationErrors = [];
        $existingLanguages = $this->persistenceLanguageHandler->loadListByLanguageCodes(
            $limitationValue->limitationValues
        );
        $missingLanguages = array_diff(
            $limitationValue->limitationValues,
            array_keys($existingLanguages)
        );
        if (!empty($missingLanguages)) {
            $validationErrors[] = new ValidationError(
                "limitationValues[] => '%languageCodes%' translation(s) do not exist",
                null,
                [
                    'languageCodes' => implode(', ', $missingLanguages),
                ]
            );
        }

        return $validationErrors;
    }

    /**
     * Create the Limitation Value.
     *
     * @param array[] $limitationValues
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    public function buildValue(array $limitationValues): APILimitationValue
    {
        return new APILanguageLimitation(['limitationValues' => $limitationValues]);
    }

    /**
     * Evaluate permission against content & target.
     *
     * {@inheritdoc}
     */
    public function evaluate(
        APILimitationValue $value,
        APIUserReference $currentUser,
        ValueObject $object,
        array $targets = null
    ): ?bool {
        if (null == $targets) {
            $targets = [];
        }

        // the main focus here is an intent to update to a new Version
        foreach ($targets as $target) {
            if (!$target instanceof Target\Version) {
                continue;
            }

            $accessVote = $this->evaluateVersionTarget($target, $value);

            // continue evaluation of targets if there was no explicit grant/deny
            if ($accessVote === self::ACCESS_ABSTAIN) {
                continue;
            }

            return $accessVote;
        }

        // in other cases we need to evaluate object
        return $this->evaluateObject($object, $value);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     *
     * @return bool|null
     */
    private function evaluateObject(ValueObject $object, APILimitationValue $value): ?bool
    {
        // by default abstain from making decision for unknown object
        $accessVote = self::ACCESS_ABSTAIN;

        // load for evaluation VersionInfo for Content & ContentInfo objects
        if ($object instanceof Content) {
            $object = $object->getVersionInfo();
        } elseif ($object instanceof ContentInfo) {
            try {
                $object = $this->persistenceContentHandler->loadVersionInfo(
                    $object->id,
                    $object->currentVersionNo
                );
            } catch (NotFoundException $e) {
                return self::ACCESS_DENIED;
            }
        }

        // cover creating Content Draft for new Content item
        if ($object instanceof ContentCreateStruct) {
            $accessVote = $this->evaluateContentCreateStruct($object, $value);
        } elseif ($object instanceof VersionInfo || $object instanceof SPIVersionInfo) {
            $accessVote = in_array($object->initialLanguageCode, $value->limitationValues)
                ? self::ACCESS_GRANTED
                : self::ACCESS_DENIED;
        }

        return $accessVote;
    }

    /**
     * Evaluate language codes of allowed translations for ContentCreateStruct.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct $object
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     *
     * @return bool|null
     */
    private function evaluateContentCreateStruct(
        ContentCreateStruct $object,
        APILimitationValue $value
    ): ?bool {
        $languageCodes = $this->getAllLanguageCodesFromCreateStruct($object);

        // check if object contains only allowed language codes
        return empty(array_diff($languageCodes, $value->limitationValues))
            ? self::ACCESS_GRANTED
            : self::ACCESS_DENIED;
    }

    /**
     * Evaluate permissions to create new Version.
     *
     * @param \eZ\Publish\SPI\Limitation\Target\Version $version
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     *
     * @return bool|null
     */
    private function evaluateVersionTarget(
        Target\Version $version,
        APILimitationValue $value
    ): ?bool {
        // intentionally evaluate all conditions separately from the least to the most important
        $accessVote = self::ACCESS_ABSTAIN;

        // allow creating new drafts
        if ($version->newStatus === VersionInfo::STATUS_DRAFT) {
            $accessVote = self::ACCESS_GRANTED;
        }

        // ... unless there's a specific list of target translations
        if (!empty($version->allLanguageCodesList)) {
            $accessVote = $this->evaluateMatchingAnyLimitation(
                $version->allLanguageCodesList,
                $value->limitationValues
            );
        }

        // ... or there's an intent to update Version
        if (!empty($version->forUpdateLanguageCodesList) || null !== $version->forUpdateInitialLanguageCode) {
            if (!empty($version->forUpdateLanguageCodesList)) {
                $diff = array_diff($version->forUpdateLanguageCodesList, $value->limitationValues);
                $accessVote = empty($diff) ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
            }

            if ($accessVote !== self::ACCESS_DENIED && null !== $version->forUpdateInitialLanguageCode) {
                $accessVote = in_array(
                    $version->forUpdateInitialLanguageCode,
                    $value->limitationValues
                )
                    ? self::ACCESS_GRANTED
                    : self::ACCESS_DENIED;
            }
        }

        return $accessVote;
    }

    /**
     * Allow access if any of the given language codes for translations matches any of the limitation values.
     *
     * @param string[] $languageCodesList
     * @param string[] $limitationValues
     *
     * @return bool
     */
    private function evaluateMatchingAnyLimitation(
        array $languageCodesList,
        array $limitationValues
    ): bool {
        return empty(array_intersect($languageCodesList, $limitationValues))
            ? self::ACCESS_DENIED
            : self::ACCESS_GRANTED;
    }

    /**
     * Get unique list of language codes for all used translations, including mainLanguageCode.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct $contentCreateStruct
     *
     * @return string[]
     */
    private function getAllLanguageCodesFromCreateStruct(
        ContentCreateStruct $contentCreateStruct
    ): array {
        $languageCodes = [$contentCreateStruct->mainLanguageCode];
        foreach ($contentCreateStruct->fields as $field) {
            $languageCodes[] = $field->languageCode;
        }

        return array_unique($languageCodes);
    }

    /**
     * Returns Criterion for use in find() query.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $currentUser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function getCriterion(
        APILimitationValue $value,
        APIUserReference $currentUser
    ): CriterionInterface {
        if (empty($value->limitationValues)) {
            // no limitation values
            throw new BadStateException(
                '$value',
                '$value->limitationValues is empty, it should not have been stored in the first place'
            );
        }

        // several limitation values: IN operation
        return new Criterion\LanguageCode($value->limitationValues);
    }

    /**
     * For LanguageLimitationType it returns an empty array because schema is not deterministic.
     *
     * @see validate for business logic.
     */
    public function valueSchema(): array
    {
        return [];
    }
}
