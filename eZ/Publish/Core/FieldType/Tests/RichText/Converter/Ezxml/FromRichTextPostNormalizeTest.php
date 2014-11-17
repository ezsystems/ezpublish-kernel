<?php
/**
 * File containing the RichText EZXML FromRichTextPostNormalize converter test
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests\RichText\Converter\Ezxml;

use eZ\Publish\Core\FieldType\RichText\Converter\Ezxml\FromRichTextPostNormalize;
use PHPUnit_Framework_TestCase;
use DOMDocument;

/**
 * Tests the FromRichTextPostNormalize converter
 */
class FromRichTextPostNormalizeTest extends PHPUnit_Framework_TestCase
{
    public function testConvert()
    {
        $input = '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/">
  <paragraph ez-temporary="1">
    <table>
      <tr>
        <td>
          <paragraph>This is a normal paragraph.</paragraph>
          <paragraph ez-temporary="1">
            <table>
              <tr>
                <td>
                  <paragraph ez-temporary="1">
                    <ol>
                      <li>
                        <paragraph ez-temporary="1">This is a list item.</paragraph>
                      </li>
                    </ol>
                  </paragraph>
                  <paragraph>This is another normal paragraph.</paragraph>
                </td>
              </tr>
            </table>
          </paragraph>
        </td>
      </tr>
    </table>
  </paragraph>
</section>
';

        $converter = new FromRichTextPostNormalize();

        $document = new DOMDocument();
        $document->loadXML( $input );

        $converterDocument = $converter->convert( $document );

        $expectedOutput = '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/">
  <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
    <table>
      <tr>
        <td>
          <paragraph>This is a normal paragraph.</paragraph>
          <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
            <table>
              <tr>
                <td>
                  <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
                    <ol>
                      <li>
                        <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">This is a list item.</paragraph>
                      </li>
                    </ol>
                  </paragraph>
                  <paragraph>This is another normal paragraph.</paragraph>
                </td>
              </tr>
            </table>
          </paragraph>
        </td>
      </tr>
    </table>
  </paragraph>
</section>
';

        $this->assertEquals( $expectedOutput, $converterDocument->saveXML() );
    }
}
