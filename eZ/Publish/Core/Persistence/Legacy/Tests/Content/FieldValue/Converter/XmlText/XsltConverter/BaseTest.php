<?php
/**
 * File containing the eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter\XmlText\XsltConverter\BaseTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter\XmlText\XsltConverter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsltConverter;
use eZ\Publish\Core\FieldType\XmlText\Validator;
use PHPUnit_Framework_TestCase;
use DOMDocument;

/**
 * Base class for XSLT converter tests.
 */
abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsltConverter
     */
    protected $converter;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsdValidator;
     */
    protected $validator;

    /**
     * @param string $inputFile
     * @param string $outputFile
     *
     * @dataProvider providerForTestConvert
     */
    public function testConvert( $inputFile, $outputFile )
    {
        $inputDocument = $this->createDocumentFromFile( $inputFile );
        $outputDocument = $this->createDocumentFromFile( $outputFile );

        $converter = $this->getConverter();
        $convertedXMLString = $converter->convert( $inputDocument );
        $convertedDocument = $this->createDocument( $convertedXMLString );

        $this->assertEquals(
            $outputDocument->saveXML(),
            $convertedDocument->saveXML()
        );

        $validator = $this->getConversionValidator();
        if ( isset( $validator ) )
        {
            $errors = $validator->validate( $convertedDocument );
            $this->assertTrue(
                empty( $errors ),
                "Conversion result did not validate against the configured schema:" .
                $this->formatValidationErrors( $errors )
            );
        }
    }

    /**
     * @param string $xmlFile
     *
     * @return \DOMDocument
     */
    protected function createDocumentFromFile( $xmlFile )
    {
        return $this->createDocument( file_get_contents( $xmlFile ) );
    }

    /**
     * @param string $xmlString
     *
     * @return \DOMDocument
     */
    protected function createDocument( $xmlString )
    {
        $document = new DOMDocument();

        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;

        $document->loadXml( $xmlString );

        return $document;
    }

    protected function formatValidationErrors( array $errors )
    {
        $output = "\n";
        foreach ( $errors as $error )
        {
            $output .= " - " . $error . "\n";
        }
        return $output;
    }

    /**
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsltConverter
     */
    protected function getConverter()
    {
        if ( $this->converter === null )
        {
            $this->converter = new XsltConverter(
                $this->getConversionTransformationStylesheet()
            );
        }

        return $this->converter;
    }

    /**
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsdValidator
     */
    protected function getConversionValidator()
    {
        $validationSchema = $this->getConversionValidationSchema();
        if ( $validationSchema !== null && $this->validator === null )
        {
            $this->validator = new Validator( $validationSchema );
        }

        return $this->validator;
    }

    /**
     * Provider for conversion test.
     *
     * @return array
     */
    abstract public function providerForTestConvert();

    /**
     * Return the absolute path to conversion transformation stylesheet.
     *
     * @return string
     */
    abstract protected function getConversionTransformationStylesheet();

    /**
     * Return the absolute path to conversion result validation schema, or null if no validation is to be performed.
     *
     * @return null|string
     */
    abstract protected function getConversionValidationSchema();
}
