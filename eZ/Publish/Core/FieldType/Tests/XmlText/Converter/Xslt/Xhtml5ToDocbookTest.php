<?php
/**
 * File containing the EzLinkToHtml5 EzXml test
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests\XmlText\Converter\Xslt;

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
     * Custom XSLT stylesheets configuration.
     *
     * @var string
     */
    static protected $customStylesheets = array(
        array(
            "path" => "eZ/Publish/Core/FieldType/Tests/XmlText/Converter/Xslt/_fixtures/xhtml5/custom_stylesheets/xhtml5_edit_to_docbook.xsl",
            "priority" => 99
        ),
    );

    /**
     *
     */
    static protected $inputDir = "eZ/Publish/Core/FieldType/Tests/XmlText/Converter/Xslt/_fixtures/xhtml5/edit";

    /**
     *
     */
    static protected $outputDir = "eZ/Publish/Core/FieldType/Tests/XmlText/Converter/Xslt/_fixtures/docbook";
}
