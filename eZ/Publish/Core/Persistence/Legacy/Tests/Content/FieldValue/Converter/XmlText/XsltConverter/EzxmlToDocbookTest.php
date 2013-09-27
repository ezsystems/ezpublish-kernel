<?php
/**
 * File containing the EzxmlToDocbook conversion test
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter\XmlText\XsltConverter;

/**
 * Tests conversion from ezxml to docbook
 */
class EzxmlToDocbookTest extends BaseTest
{
    /**
     * Provider for conversion test.
     *
     * @return array
     */
    public function providerForTestConvert()
    {
        $map = array();

        foreach ( glob( __DIR__ . "/_fixtures/*.xml" ) as $xmlFile )
        {
            $map[] = array(
                $xmlFile,
                __DIR__ . "/../../../../../../../../FieldType/Tests/XmlText/Converter/Xslt/_fixtures/docbook/" . basename( $xmlFile )
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
        return __DIR__ . "/../../../../../../Content/FieldValue/Converter/XmlText/Resources/stylesheets/ezxml_docbook.xsl";
    }

    /**
     * Return the absolute path to conversion result validation schema, or null if no validation is to be performed.
     *
     * @return null|string
     */
    protected function getConversionValidationSchema()
    {
        return __DIR__ . "/../../../../../../../../FieldType/XmlText/Resources/schemas/docbook/ezpublish.rng";
    }
}
