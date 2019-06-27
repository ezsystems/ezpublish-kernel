<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Limitation\Target\Builder;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\SPI\Limitation\Target;

/**
 * Builder of \eZ\Publish\SPI\Limitation\Target\Version instance.
 *
 * @see \eZ\Publish\SPI\Limitation\Target\Version
 */
final class VersionBuilder
{
    /** @var array */
    private $targetVersionProperties = [];

    public function build(): Target\Version
    {
        return new Target\Version($this->targetVersionProperties);
    }

    /**
     * Set intent to translate, to an unspecified (yet) language, any from the given list.
     *
     * @param array $languageCodes
     *
     * @return self
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function translateToAnyLanguageOf(array $languageCodes): self
    {
        foreach ($languageCodes as $languageCode) {
            if (!is_string($languageCode) || empty($languageCode)) {
                throw new InvalidArgumentException('$languageCodes', 'All language codes should be non-empty strings');
            }
        }

        $this->targetVersionProperties['allLanguageCodesList'] = $languageCodes;

        return $this;
    }

    /**
     * Set intent to create Content from unspecified (yet) content type, any from the given list.
     *
     * @param int[] $contentTypeIds
     *
     * @return self
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function createFromAnyContentTypeOf(array $contentTypeIds): self
    {
        foreach ($contentTypeIds as $contentTypeId) {
            if (!\is_int($contentTypeId)) {
                throw new InvalidArgumentException('$contentTypeIds', 'All contentType ids should be integers');
            }
        }

        $this->targetVersionProperties['allContentTypeIdsList'] = $contentTypeIds;

        return $this;
    }

    /**
     * Set intent to change Version status.
     *
     * Supported: <code>VersionInfo::STATUS_DRAFT, VersionInfo::STATUS_PUBLISHED, VersionInfo::STATUS_ARCHIVED</code>
     *
     * @see \eZ\Publish\API\Repository\Values\Content\VersionInfo
     *
     * @param int $status
     *
     * @return self
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function changeStatusTo(int $status): self
    {
        if (!in_array(
            $status,
            [VersionInfo::STATUS_DRAFT, VersionInfo::STATUS_PUBLISHED, VersionInfo::STATUS_ARCHIVED]
        )) {
            throw new InvalidArgumentException(
                '$status',
                'Status should be one of the following: STATUS_DRAFT, STATUS_PUBLISHED, STATUS_ARCHIVED'
            );
        }

        $this->targetVersionProperties['newStatus'] = $status;

        return $this;
    }

    /**
     * Set intent to update Content Version Fields.
     *
     * @param string|null $initialLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $fields
     *
     * @return self
     */
    public function updateFieldsTo(?string $initialLanguageCode, array $fields): self
    {
        $languageCodes = array_map(
            function (Field $field) {
                return $field->languageCode;
            },
            $fields
        );

        $this->targetVersionProperties['forUpdateInitialLanguageCode'] = $initialLanguageCode;
        $this->targetVersionProperties['forUpdateLanguageCodesList'] = array_values(
            array_unique($languageCodes)
        );

        return $this;
    }
}
