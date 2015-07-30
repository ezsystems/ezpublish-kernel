<?php

/**
 * File containing the EmbedLinking converter test.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Tests\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter\EmbedLinking;
use PHPUnit_Framework_TestCase;
use DOMDocument;

/**
 * Tests the EmbedLinking converter.
 */
class EmbedLinkingTest extends PHPUnit_Framework_TestCase
{
    /**
     * Provider for conversion test.
     *
     * @return array
     */
    public function providerForTestConvert()
    {
        $map = array();

        foreach (glob(__DIR__ . '/_fixtures/embed_linking/input/*.xml') as $inputFilePath) {
            $basename = basename($inputFilePath, '.xml');
            $outputFilePath = __DIR__ . "/_fixtures/embed_linking/output/{$basename}.xml";

            $map[] = array($inputFilePath, $outputFilePath);
        }

        return $map;
    }

    /**
     * @param string $xml
     * @param bool $isPath
     *
     * @return \DOMDocument
     */
    protected function createDocument($xml, $isPath = true)
    {
        $document = new DOMDocument();

        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;

        if ($isPath === true) {
            $xml = file_get_contents($xml);
        }

        $document->loadXml($xml);

        return $document;
    }

    /**
     * @param string $inputFilePath
     * @param string $outputFilePath
     *
     * @dataProvider providerForTestConvert
     */
    public function testConvert($inputFilePath, $outputFilePath)
    {
        $inputDocument = $this->createDocument($inputFilePath);

        $converter = new EmbedLinking();
        $converter->convert($inputDocument);

        $outputDocument = $this->createDocument($outputFilePath);

        $this->assertEquals(
            $outputDocument,
            $inputDocument
        );
    }
}
