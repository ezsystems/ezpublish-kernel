<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\ImageAsset;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\FieldType\Nameable;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\Handler as SPIContentHandler;

/**
 * Class NameableField for ImageAsset FieldType.
 */
class NameableField implements Nameable
{
    /** @var \eZ\Publish\SPI\Persistence\Content\Handler */
    private $handler;

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $handler
     */
    public function __construct(SPIContentHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param \eZ\Publish\Core\FieldType\ImageAsset\Value $value
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param string $languageCode
     *
     * @return string
     */
    public function getFieldName(SPIValue $value, FieldDefinition $fieldDefinition, $languageCode)
    {
        if (empty($value->destinationContentId)) {
            return '';
        }

        try {
            $contentInfo = $this->handler->loadContentInfo($value->destinationContentId);
            $versionInfo = $this->handler->loadVersionInfo($value->destinationContentId, $contentInfo->currentVersionNo);
        } catch (NotFoundException $e) {
            return '';
        }

        if (isset($versionInfo->names[$languageCode])) {
            return $versionInfo->names[$languageCode];
        }

        return $versionInfo->names[$contentInfo->mainLanguageCode];
    }
}
