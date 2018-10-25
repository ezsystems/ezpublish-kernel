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

class RelationProcessor implements RelationProcessorInterface
{
    private const EMBED_TAG_NAMES = [
        'ezembedinline',
        'ezembed'
    ];

    private const LINK_TAG_NAMES = [
        'link',
        'ezlink'
    ];

    /**
     * {@inheritdoc}
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
    protected function getRelatedObjectIds(DOMDocument $xml, array $tagNames): array
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
