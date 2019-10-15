<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\CompareService as ComparingServiceInterface;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldDiff;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\VersionDiff;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Compare\CompareEngineRegistry;
use eZ\Publish\Core\Compare\FieldRegistry;
use eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;

class CompareService implements ComparingServiceInterface
{
    /** @var \eZ\Publish\SPI\Persistence\Content\Handler */
    private $contentHandler;

    /** @var \eZ\Publish\SPI\Persistence\Content\Type\Handler */
    private $contentTypeHandler;

    /** @var \eZ\Publish\Core\Compare\FieldRegistry */
    private $fieldRegistry;

    /** @var \eZ\Publish\Core\Compare\CompareEngineRegistry */
    private $comparatorEngineRegistry;

    /** @var \eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper */
    private $contentTypeDomainMapper;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    public function __construct(
        ContentHandler $contentHandler,
        ContentTypeHandler $contentTypeHandler,
        FieldRegistry $fieldRegistry,
        CompareEngineRegistry $comparatorEngineRegistry,
        ContentTypeDomainMapper $contentTypeDomainMapper,
        PermissionResolver $permissionResolver
    ) {
        $this->contentHandler = $contentHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldRegistry = $fieldRegistry;
        $this->comparatorEngineRegistry = $comparatorEngineRegistry;
        $this->contentTypeDomainMapper = $contentTypeDomainMapper;
        $this->permissionResolver = $permissionResolver;
    }

    public function compareVersions(
        VersionInfo $versionA,
        VersionInfo $versionB,
        ?string $languageCode = null
    ): VersionDiff {
        if ($versionA->contentInfo->id !== $versionB->contentInfo->id) {
            throw new InvalidArgumentException(
                '$versionB',
                'Version B is not version of the same content as $versionA'
            );
        }

        if (!$this->permissionResolver->canUser('content', 'versionread', $versionA)) {
            throw new UnauthorizedException('content', 'versionread', ['contentId' => $versionA->contentInfo->id]);
        }

        $languageCode = $languageCode ?? $versionA->initialLanguageCode;
        $content = $this->contentHandler->load($versionA->getContentInfo()->id, $versionA->versionNo, [$languageCode]);
        $contentToCompare = $this->contentHandler->load($versionB->getContentInfo()->id, $versionB->versionNo, [$languageCode]);
        $fieldsDiff = [];
        foreach ($content->fields as $field) {
            $comparableField = $this->fieldRegistry->getType($field->type);
            $matchingField = $this->getMatchingField($field, $contentToCompare);
            $fieldDefinition = $this->contentTypeHandler->getFieldDefinition($field->fieldDefinitionId, Type::STATUS_DEFINED);
            $dataA = $comparableField->getDataToCompare(
                $field->value
            );
            $dataB = $comparableField->getDataToCompare(
                $matchingField->value
            );
            $diffs = [];
            foreach ($dataA as $name => $fieldAData) {
                $engine = $this->comparatorEngineRegistry->getEngine(get_class($fieldAData));

                $diffs[$name] = $engine->compareFieldsData($fieldAData, $dataB[$name]);
            }
            $fieldsDiff[$fieldDefinition->identifier] = new FieldDiff(
                $this->contentTypeDomainMapper->buildFieldDefinitionDomainObject(
                    $fieldDefinition,
                    $languageCode
                ),
                $diffs
            );
        }

        return new VersionDiff($fieldsDiff);
    }

    private function getMatchingField(
        Field $field,
        Content $contentToCompare
    ): Field {
        foreach ($contentToCompare->fields as $fieldToCompare) {
            if ($fieldToCompare->fieldDefinitionId === $field->fieldDefinitionId) {
                return $fieldToCompare;
            }
        }
    }
}
