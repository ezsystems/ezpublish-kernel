<?php

/**
 * File containing the NameableField class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RelationList;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\FieldType\Nameable;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\Handler as SPIContentHandler;

/**
 * Class NameableField for RelationList FieldType.
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
     * @param \eZ\Publish\Core\FieldType\RelationList\Value $value
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param string $languageCode
     *
     * @return string
     */
    public function getFieldName(SPIValue $value, FieldDefinition $fieldDefinition, $languageCode)
    {
        if (empty($value->destinationContentIds)) {
            return '';
        }

        $names = [];
        foreach ($value->destinationContentIds as $contentId) {
            try {
                $contentInfo = $this->handler->loadContentInfo($contentId);
                $versionInfo = $this->handler->loadVersionInfo($contentId, $contentInfo->currentVersionNo);
            } catch (NotFoundException $e) {
                continue;
            }

            if (isset($versionInfo->names[$languageCode])) {
                $names[] = $versionInfo->names[$languageCode];
            } else {
                $names[] = $versionInfo->names[$contentInfo->mainLanguageCode];
            }
        }

        return implode(' ', $names);
    }
}
