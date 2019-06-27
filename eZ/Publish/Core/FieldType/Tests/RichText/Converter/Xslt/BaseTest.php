<?php

/**
 * File containing the eZ\Publish\Core\FieldType\Tests\RichText\Converter\BaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\RichText\Converter\Xslt;

use eZ\Publish\Core\FieldType\RichText\Converter\Xslt;
use eZ\Publish\Core\FieldType\RichText\Validator;
use PHPUnit\Framework\TestCase;
use DOMDocument;
use DOMXpath;

/**
 * Base class for XSLT converter tests.
 */
abstract class BaseTest extends TestCase
{
    /** @var \eZ\Publish\Core\FieldType\RichText\Converter */
    protected $converter;

    /** @var \eZ\Publish\Core\FieldType\RichText\Validator */
    protected $validator;

    /**
     * Provider for conversion test.
     *
     * @return array
     */
    public function providerForTestConvert()
    {
        $fixtureSubdirectories = $this->getFixtureSubdirectories();

        $map = [];

        foreach (glob(__DIR__ . "/_fixtures/{$fixtureSubdirectories['input']}/*.xml") as $inputFile) {
            $basename = basename($inputFile, '.xml');
            $outputFile = __DIR__ . "/_fixtures/{$fixtureSubdirectories['output']}/{$basename}.xml";
            $outputFileLossy = __DIR__ . "/_fixtures/{$fixtureSubdirectories['output']}/{$basename}.lossy.xml";

            if (!file_exists($outputFile) && file_exists($outputFileLossy)) {
                $outputFile = $outputFileLossy;
            }

            $map[] = [$inputFile, $outputFile];
        }

        $lossySubdirectory = "_fixtures/{$fixtureSubdirectories['input']}/lossy";
        $inputDirNormalized = str_replace('/', '.', $fixtureSubdirectories['input']);
        $outputDirNormalized = str_replace('/', '.', $fixtureSubdirectories['output']);
        foreach (glob(__DIR__ . "/{$lossySubdirectory}/*.{$inputDirNormalized}.xml") as $inputFile) {
            $basename = basename(basename($inputFile, '.xml'), ".{$inputDirNormalized}");
            $outputFile = __DIR__ . "/{$lossySubdirectory}/{$basename}.{$outputDirNormalized}.xml";

            if (!file_exists($outputFile)) {
                continue;
            }

            $map[] = [$inputFile, $outputFile];
        }

        return $map;
    }

    protected function removeComments(DOMDocument $document)
    {
        $xpath = new DOMXpath($document);
        $nodes = $xpath->query('//comment()');

        for ($i = 0; $i < $nodes->length; ++$i) {
            $nodes->item($i)->parentNode->removeChild($nodes->item($i));
        }
    }

    /**
     * @param string $inputFile
     * @param string $outputFile
     *
     * @dataProvider providerForTestConvert
     */
    public function testConvert($inputFile, $outputFile)
    {
        $endsWith = '.lossy.xml';
        if (substr_compare($inputFile, $endsWith, -strlen($endsWith), strlen($endsWith)) === 0) {
            $this->markTestSkipped('Skipped lossy conversion.');
        }

        if (!file_exists($outputFile)) {
            $this->markTestIncomplete('Test is not complete: missing output fixture: ' . $outputFile);
        }

        $inputDocument = $this->createDocument($inputFile);
        $outputDocument = $this->createDocument($outputFile);

        $this->removeComments($inputDocument);
        $this->removeComments($outputDocument);

        $converter = $this->getConverter();
        $convertedDocument = $converter->convert($inputDocument);

        // Needed by some disabled output escaping (eg. legacy ezxml paragraph <line/> elements)
        $convertedDocumentNormalized = new DOMDocument();
        $convertedDocumentNormalized->loadXML($convertedDocument->saveXML());

        $this->assertEquals(
            $outputDocument,
            $convertedDocumentNormalized,
            sprintf(
                "Failed asserting that two DOM documents are equal.\nInput file: %s\nOutput file %s",
                $inputFile,
                $outputFile
            )
        );

        $validator = $this->getConversionValidator();
        if (isset($validator)) {
            // As assert below validated converted and output is the same, validate ouput here to get right line number.
            $errors = $validator->validate($outputDocument);
            $this->assertEmpty(
                $errors,
                'Conversion result did not validate against the configured schemas:' .
                $this->formatValidationErrors($outputFile, $errors)
            );
        }
    }

    /**
     * @param string $xmlFile
     *
     * @return \DOMDocument
     */
    protected function createDocument($xmlFile)
    {
        $document = new DOMDocument();

        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;

        $document->loadXml(file_get_contents($xmlFile), LIBXML_NOENT);

        return $document;
    }

    protected function formatValidationErrors($outputFile, array $errors)
    {
        $output = "\n";
        foreach ($errors as $error) {
            $output .= ' - ' . $error . "\n";
        }
        $output .= "Configured schemas:\n";
        foreach ($this->getConversionValidationSchema() as $schemaPath) {
            $output .= ' - ' . $schemaPath . "\n";
        }
        $output .= "Validated XML:\n" . file_get_contents($outputFile);

        return $output;
    }

    /**
     * @return \eZ\Publish\Core\FieldType\RichText\Converter\Xslt
     */
    protected function getConverter()
    {
        if ($this->converter === null) {
            $this->converter = new Xslt(
                $this->getConversionTransformationStylesheet(),
                $this->getCustomConversionTransformationStylesheets()
            );
        }

        return $this->converter;
    }

    /**
     * @return \eZ\Publish\Core\FieldType\RichText\Validator
     */
    protected function getConversionValidator()
    {
        $validationSchema = $this->getConversionValidationSchema();
        if ($validationSchema !== null && $this->validator === null) {
            $this->validator = new Validator($validationSchema);
        }

        return $this->validator;
    }

    /**
     * Returns subdirectories for input and output fixtures.
     *
     * The test will try to match each XML file in input directory with
     * the file of the same name in the output directory.
     *
     * It is possible to test lossy conversion as well (say legacy ezxml).
     * To use this filename of the fixture that is converted with data loss
     * needs to end with `.lossy.xml`. As input test with this fixture will
     * be skipped, but as output fixture it will be matched to the input
     * fixture file of the same name but without `.lossy` part.
     *
     * If input file could not be matched with output file, test will be
     * marked as incomplete, meaning pairing of fixtures is expected.
     *
     * To implement additional tests for  lossy conversion put the test
     * fixtures inside "lossy" subdirectory in the input directory. This
     * directory needs to contain both source and destination fixtures, matched
     * by the filename and part of the filename directly before the file extension.
     * This part of the filename will be matched from the name of fixture
     * subdirectories.
     *
     * Example for conversion from ezxml to docbook:
     *
     *      .../_fixtures/ezxml/lossy/001-sectionNested.ezxml.xml
     *
     * will be converted to with:
     *
     *      .../_fixtures/ezxml/lossy/001-sectionNested.docbook.xml
     *
     * Comments in fixtures are removed before conversion, so be free to use
     * comments inside fixtures for documentation as needed.
     *
     * Example:
     * <code>
     *  return array(
     *      "input" => "docbook",
     *      "output" => "ezxml"
     *  );
     * </code>
     *
     * @return array
     */
    abstract public function getFixtureSubdirectories();

    /**
     * Return the absolute path to conversion transformation stylesheet.
     *
     * @return string
     */
    abstract protected function getConversionTransformationStylesheet();

    /**
     * Return custom XSLT stylesheets configuration.
     *
     * Stylesheet paths must be absolute.
     *
     * Code example:
     *
     * <code>
     *  array(
     *      array(
     *          "path" => __DIR__ . "/core.xsl",
     *          "priority" => 100
     *      ),
     *      array(
     *          "path" => __DIR__ . "/custom.xsl",
     *          "priority" => 99
     *      ),
     *  )
     * </code>
     *
     * @return array
     */
    protected function getCustomConversionTransformationStylesheets()
    {
        return [];
    }

    /**
     * Return an array of absolute paths to conversion result validation schemas.
     *
     * @return string[]
     */
    protected function getConversionValidationSchema()
    {
        return [];
    }
}
