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
use PHPUnit_Framework_TestCase;
use DOMDocument;

/**
 * Base class for XSLT converter tests.
 */
abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * Path to the XSLT file.
     *
     * @var string
     */
    static protected $stylesheet;

    /**
     * Directory with input fixtures.
     *
     * @var string
     */
    static protected $inputDir;

    /**
     * Directory with expected conversion results.
     *
     * @var string
     */
    static protected $outputDir;

    public function getXmlFixtures()
    {
        $fixtures = array();

        foreach ( glob( self::getInstallationDir() . "/" . static::$inputDir . "/*.xml" ) as $xmlFile )
        {
            $fixtures[] = array(
                $xmlFile,
                self::getInstallationDir() . "/" . static::$outputDir . "/" . basename( $xmlFile )
            );
        }

        return $fixtures;
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

    /**
     * @param string $inputFile
     * @param string $outputFile
     *
     * @dataProvider getXmlFixtures
     */
    public function testConvert( $inputFile, $outputFile )
    {
        $inputDocument = $this->createDocument( $inputFile );
        $expectedOutputDocument = $this->createDocument( $outputFile );

        $converter = $this->getConverter();

        $outputDocument = new DOMDocument();
        $outputDocument->preserveWhiteSpace = false;
        $outputDocument->formatOutput = false;
        $outputDocument->loadXml( $converter->convert( $inputDocument ) );

        $this->assertEquals(
            $expectedOutputDocument->saveXML(),
            $outputDocument->saveXML()
        );
    }

    /**
     * @return string
     */
    protected function getStylesheetPath()
    {
        return self::getInstallationDir() . "/" . static::$stylesheet;
    }

    /**
     * @return string
     */
    static protected function getInstallationDir()
    {
        static $installDir = null;
        if ( $installDir === null )
        {
            $config = require 'config.php';
            $installDir = $config['service']['parameters']['install_dir'];
        }
        return $installDir;
    }

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsltConverter
     */
    protected $converter;

    /**
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsltConverter
     */
    protected function getConverter()
    {
        if ( $this->converter === null )
        {
            $this->converter = new XsltConverter( $this->getStylesheetPath() );
        }

        return $this->converter;
    }
}
