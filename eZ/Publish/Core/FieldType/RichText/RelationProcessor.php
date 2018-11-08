<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\RichText;

use DOMDocument;
use DOMXPath;
use eZ\Publish\API\Repository\Values\Content\Relation;

final class RelationProcessor
{
    private const EMBED_TAG_NAMES = [
        'ezembedinline', 'ezembed',
    ];

    private const LINK_TAG_NAMES = [
        'link', 'ezlink',
    ];

    /**
     * Returns relation data extracted from value.
     *
     * Not intended for \eZ\Publish\API\Repository\Values\Content\Relation::COMMON type relations,
     * there is a service API for handling those.
     *
     * @param \DOMDocument $doc
     *
     * @return array Hash with relation type as key and array of destination content ids as value.
     *
     * Example:
     * <code>
     *  array(
     *      \eZ\Publish\API\Repository\Values\Content\Relation::LINK => array(
     *          "contentIds" => array( 12, 13, 14 ),
     *          "locationIds" => array( 24 )
     *      ),
     *      \eZ\Publish\API\Repository\Values\Content\Relation::EMBED => array(
     *          "contentIds" => array( 12 ),
     *          "locationIds" => array( 24, 45 )
     *      ),
     *      \eZ\Publish\API\Repository\Values\Content\Relation::FIELD => array( 12 )
     *  )
     * </code>
     */
    public function getRelations(DOMDocument $doc): array
    {
        return [
            Relation::LINK => $this->getRelatedObjectIds($doc, self::LINK_TAG_NAMES),
            Relation::EMBED => $this->getRelatedObjectIds($doc, self::EMBED_TAG_NAMES),
        ];
    }

    /**
     * @param \DOMDocument $xml
     * @param array $tagNames
     *
     * @return array
     */
    private function getRelatedObjectIds(DOMDocument $xml, array $tagNames): array
    {
        $contentIds = [];
        $locationIds = [];

        $xpath = new DOMXPath($xml);
        $xpath->registerNamespace('docbook', 'http://docbook.org/ns/docbook');
        foreach ($tagNames as $tagName) {
            $xpathExpression = "//docbook:{$tagName}[starts-with( @xlink:href, 'ezcontent://' ) or starts-with( @xlink:href, 'ezlocation://' )]";
            /** @var \DOMElement $element */
            foreach ($xpath->query($xpathExpression) as $element) {
                preg_match('~^(.+)://([^#]*)?(#.*|\\s*)?$~', $element->getAttribute('xlink:href'), $matches);
                list(, $scheme, $id) = $matches;

                if (empty($id)) {
                    continue;
                }

                if ($scheme === 'ezcontent') {
                    $contentIds[] = $id;
                } elseif ($scheme === 'ezlocation') {
                    $locationIds[] = $id;
                }
            }
        }

        return [
            'locationIds' => array_unique($locationIds),
            'contentIds' => array_unique($contentIds),
        ];
    }
}
