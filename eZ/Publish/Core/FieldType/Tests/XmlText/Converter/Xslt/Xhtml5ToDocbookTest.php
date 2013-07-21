<?php
/**
 * File containing the EzLinkToHtml5 EzXml test
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType\XmlText\Converter\Xslt;

use eZ\Publish\Core\FieldType\Tests\XmlText\Converter\Xslt\BaseTest;

/**
 *
 */
class Xhtml5ToDocbookTest extends BaseTest
{
    /**
     *
     */
    static protected $stylesheet = "eZ/Publish/Core/FieldType/XmlText/Resources/stylesheets/xhtml5/docbook.xsl";

    /**
     *
     */
    static protected $inputDir = "eZ/Publish/Core/FieldType/Tests/XmlText/Converter/Xslt/_fixtures/xhtml5";

    /**
     *
     */
    static protected $outputDir = "eZ/Publish/Core/FieldType/Tests/XmlText/Converter/Xslt/_fixtures/docbook";
}
