<?php
/**
 * File containing the DocbookToXhtml5Edit conversion test
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests\XmlText\Converter\Xslt;

/**
 * Tests conversion from docbook to xhtml5 edit format
 */
class DocbookToXhtml5EditTest extends BaseTest
{
    /**
     * Provider for conversion test.
     *
     * @return array
     */
    public function providerForTestConvert()
    {
        $map = array();

        foreach ( glob( __DIR__ . "/_fixtures/docbook/*.xml" ) as $xmlFile )
        {
            $map[] = array(
                $xmlFile,
                __DIR__ . "/_fixtures/xhtml5/edit/" . basename( $xmlFile )
            );
        }

        return $map;
    }

    /**
     * Return the absolute path to conversion transformation stylesheet.
     *
     * @return string
     */
    protected function getConversionTransformationStylesheet()
    {
        return __DIR__ . "/../../../../XmlText/Resources/stylesheets/docbook/xhtml5/xhtml5.xsl";
    }

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
        return array(
            array(
                "path" => __DIR__ . "/../../../../XmlText/Resources/stylesheets/docbook/xhtml5/core.xsl",
                "priority" => 100
            ),
            array(
                "path" => __DIR__ . "/_fixtures/docbook/custom_stylesheets/docbook_to_xhtml5_edit.xsl",
                "priority" => 99
            ),
        );
    }

    /**
     * Return the absolute path to conversion result validation schema, or null if no validation is to be performed.
     *
     * @return null|string
     */
    protected function getConversionValidationSchema()
    {
        return __DIR__ . "/../../../../XmlText/Resources/schemas/ezxhtml5/ezxhtml5.xsd";
    }
}
