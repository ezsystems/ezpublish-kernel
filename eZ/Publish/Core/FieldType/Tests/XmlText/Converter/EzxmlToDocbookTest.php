<?php
/**
 * File containing the EzLinkToHtml5 EzXml test
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter\EzxmlToDocbook;
use PHPUnit_Framework_TestCase;

/**
 */
class EzxmlToDocbookTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function providerForTestConvert()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/">
  <paragraph>This is a paragraph.</paragraph>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" version="5.0">
  <para>This is a paragraph.</para>
</article>
',
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/">
  <heading>This is a... heading.</heading>
  <paragraph>This is a paragraph.</paragraph>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" version="5.0">
  <title>This is a... heading.</title>
  <para>This is a paragraph.</para>
</article>
',
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/">
  <heading>This is a... heading.</heading>
  <paragraph>This is a paragraph.</paragraph>
  <section>
    <heading>This is a second heading.</heading>
    <paragraph>This is a second paragraph.</paragraph>
  </section>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" version="5.0">
  <title>This is a... heading.</title>
  <para>This is a paragraph.</para>
  <section>
    <title>This is a second heading.</title>
    <para>This is a second paragraph.</para>
  </section>
</article>
',
            ),
        );
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @covers \eZ\Publish\Core\FieldType\XmlText\Converter\EzxmlToDocbook::convert
     *
     * @dataProvider providerForTestConvert
     *
     * @param $sourceXmlString
     * @param $expectedXmlString
     */
    public function testConvert( $sourceXmlString, $expectedXmlString )
    {
        $sourceXmlDoc = new \DOMDocument();
        $sourceXmlDoc->loadXML( $sourceXmlString );

        $converter = $this->getConverter();
        $convertedXmlString = $converter->convert( $sourceXmlDoc );

        $this->assertEquals( $expectedXmlString, $convertedXmlString );
    }

    /**
     * @var \eZ\Publish\Core\FieldType\XmlText\Converter\EzxmlToDocbook
     */
    protected $converter;

    /**
     * @return \eZ\Publish\Core\FieldType\XmlText\Converter\EzxmlToDocbook
     */
    protected function getConverter()
    {
        if ( $this->converter === null )
        {
            $installationDir = self::getInstallationDir();
            $stylesheet = "eZ/Publish/Core/FieldType/XmlText/Converter/Resources/stylesheets/ezxml_docbook.xsl";
            $this->converter = new EzxmlToDocbook( $installationDir . "/" . $stylesheet );
        }

        return $this->converter;
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
}
