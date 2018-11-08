<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Tests\RichText;

use DOMDocument;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\Core\FieldType\RichText\RelationProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @group fieldType
 * @group ezrichtext
 */
class RelationProcessorTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\FieldType\RichText\RelationProcessor::getRelations
     *
     * @dataProvider dateProviderForGetRelations
     *
     * @param \DOMDocument $document
     * @param array $expectedRelations
     */
    public function testGetRelations(DOMDocument $document, array $expectedRelations): void
    {
        $actualProcessor = (new RelationProcessor())->getRelations($document);

        $this->assertEquals($expectedRelations, $actualProcessor);
    }

    public function dateProviderForGetRelations(): array
    {
        $xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0-variant ezpublish-1.0">
    <title>Some text</title>
    <para><link xlink:href="ezlocation://72">link1</link></para>
    <para><link xlink:href="ezlocation://61">link2</link></para>
    <para><link xlink:href="ezlocation://61">link3</link></para>
    <para><link xlink:href="ezcontent://70">link4</link></para>
    <para><link xlink:href="ezcontent://75">link5</link></para>
    <para><link xlink:href="ezcontent://75">link6</link></para>
</section>
EOT;

        return [
            [
                $this->createDOMDocument($xml),
                [
                    Relation::LINK => [
                        'locationIds' => [72, 61],
                        'contentIds' => [70, 75],
                    ],
                    Relation::EMBED => [
                        'locationIds' => [],
                        'contentIds' => [],
                    ],
                ],
            ],
        ];
    }

    private function createDOMDocument(string $xml): DOMDocument
    {
        $document = new DOMDocument();
        $document->loadXML($xml);

        return $document;
    }
}
