<?php
/**
 * File containing the EzLinkToHtml5 EzXml test
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter\DocbookToXhtml5;
use PHPUnit_Framework_TestCase;

/**
 */
class DocbookToXhtml5Test extends PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function providerForTestConvert()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" version="5.0">
  <title>This is a heading.</title>
  <para>This is a paragraph.</para>
</article>',
                '
  <h2>This is a heading.</h2>
  <p>This is a paragraph.</p>

',
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" version="5.0">
  <title>This is a heading.</title>
  <para>This is a paragraph.</para>
  <section>
    <title>This is a second heading.</title>
    <para>This is a second paragraph.</para>
  </section>
</article>',
                '
  <h2>This is a heading.</h2>
  <p>This is a paragraph.</p>
  <section>
    <h3>This is a second heading.</h3>
    <p>This is a second paragraph.</p>
  </section>

',
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" version="5.0">
  <title>This is a heading.</title>
  <para>This is a paragraph.</para>
  <section>
    <title>This is a second heading.</title>
    <para>This is a second paragraph.</para>
    <section>
      <title>This is a third heading.</title>
      <para>This is a third paragraph.</para>
      <section>
        <title>This is a fourth heading.</title>
        <para>This is a fourth paragraph.</para>
        <section>
          <title>This is a fifth heading.</title>
          <para>This is a fifth paragraph.</para>
          <section>
            <title>This is a sixth heading.</title>
            <para>This is a sixth paragraph.</para>
          </section>
        </section>
      </section>
    </section>
  </section>
</article>',
                '
  <h2>This is a heading.</h2>
  <p>This is a paragraph.</p>
  <section>
    <h3>This is a second heading.</h3>
    <p>This is a second paragraph.</p>
    <section>
      <h4>This is a third heading.</h4>
      <p>This is a third paragraph.</p>
      <section>
        <h5>This is a fourth heading.</h5>
        <p>This is a fourth paragraph.</p>
        <section>
          <h6>This is a fifth heading.</h6>
          <p>This is a fifth paragraph.</p>
          <section>
            <h6>This is a sixth heading.</h6>
            <p>This is a sixth paragraph.</p>
          </section>
        </section>
      </section>
    </section>
  </section>

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
     * @var \eZ\Publish\Core\FieldType\XmlText\Converter\DocbookToXhtml5
     */
    protected $converter;

    /**
     * @return \eZ\Publish\Core\FieldType\XmlText\Converter\DocbookToXhtml5
     */
    protected function getConverter()
    {
        if ( $this->converter === null )
        {
            $installationDir = self::getInstallationDir();
            $stylesheet = "eZ/Publish/Core/FieldType/XmlText/Converter/Resources/stylesheets/docbook_xhtml5.xsl";
            $this->converter = new DocbookToXhtml5( $installationDir . "/" . $stylesheet );
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
