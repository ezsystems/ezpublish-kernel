<?php
/**
 * File containing the eZ\Publish\Core\FieldType\Tests\XmlText\Converter\BaseTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests\XmlText\Converter\Xslt;

use eZ\Publish\Core\FieldType\XmlText\Converter\Xslt;
use eZ\Publish\Core\FieldType\XmlText\Validator;
use PHPUnit_Framework_TestCase;
use DOMDocument;

/**
 * Base class for XSLT converter tests.
 */
abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\FieldType\XmlText\Converter
     */
    protected $converter;

    /**
     * @var \eZ\Publish\Core\FieldType\XmlText\Validator
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
        $inputDocument = $this->createDocument( $inputFile );
        $outputDocument = $this->createDocument( $outputFile );

        $converter = $this->getConverter();
        $convertedDocument = $converter->convert( $inputDocument );

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
    protected function createDocument( $xmlFile )
    {
        $document = new DOMDocument();

        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;

        $document->loadXml( file_get_contents( $xmlFile ) );

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
     * @return \eZ\Publish\Core\FieldType\XmlText\Converter\Xslt
     */
    protected function getConverter()
    {
        if ( $this->converter === null )
        {
            $this->converter = new Xslt(
                $this->getConversionTransformationStylesheet(),
                $this->getCustomConversionTransformationStylesheets()
            );
        }

        return $this->converter;
    }

    /**
     * @return \eZ\Publish\Core\FieldType\XmlText\Validator
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
    abstract protected function getCustomConversionTransformationStylesheets();

    /**
     * Return the absolute path to conversion result validation schema, or null if no validation is to be performed.
     *
     * @return null|string
     */
    abstract protected function getConversionValidationSchema();
}
