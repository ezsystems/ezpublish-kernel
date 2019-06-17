<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\FieldType;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\ImageAssetConverter;
use eZ\Publish\SPI\Persistence\Content;

class ImageAssetIntegrationTest extends BaseIntegrationTest
{
    /**
     * {@inheritdoc}
     */
    public function getTypeName()
    {
        return FieldType\ImageAsset\Type::FIELD_TYPE_IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomHandler()
    {
        $contentService = $this->createMock(ContentService::class);
        $locationService = $this->createMock(LocationService::class);
        $contentTypeService = $this->createMock(ContentTypeService::class);
        $contentHandler = $this->createMock(Content\Handler::class);

        $config = [];

        $mapper = new FieldType\ImageAsset\AssetMapper(
            $contentService,
            $locationService,
            $contentTypeService,
            $config
        );

        $fieldType = new FieldType\ImageAsset\Type(
            $contentService,
            $contentTypeService,
            $mapper,
            $contentHandler
        );

        $fieldType->setTransformationProcessor($this->getTransformationProcessor());

        return $this->getHandler(
            'ezimageasset',
            $fieldType,
            new ImageAssetConverter(),
            new FieldType\NullStorage()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeConstraints()
    {
        return new Content\FieldTypeConstraints();
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldDefinitionData()
    {
        return [
            ['fieldType', 'ezimageasset'],
            ['fieldTypeConstraints', new Content\FieldTypeConstraints(['fieldSettings' => null])],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialValue()
    {
        return new Content\FieldValue(
            [
                'data' => [
                    'destinationContentId' => 1,
                    'alternativeText' => null,
                ],
                'externalData' => null,
                'sortKey' => null,
            ],
            [
                'data' => [
                    'destinationContentId' => 1,
                    'alternativeText' => 'The alternative text',
                ],
                'externalData' => null,
                'sortKey' => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedValue()
    {
        return new Content\FieldValue(
            [
                'data' => [
                    'destinationContentId' => 2,
                    'alternativeText' => null,
                ],
                'externalData' => null,
                'sortKey' => null,
            ],
            [
                'data' => [
                    'destinationContentId' => 2,
                    'alternativeText' => 'The alternative text',
                ],
                'externalData' => null,
                'sortKey' => null,
            ]
        );
    }
}
