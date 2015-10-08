<?php

/**
 * File containing the DocbookToXhtml5Edit conversion test.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Tests\RichText\Converter\Xslt;

/**
 * Tests conversion from docbook to xhtml5 edit format.
 */
class DocbookToXhtml5EditTest extends BaseTest
{
    /**
     * Returns subdirectories for input and output fixtures.
     *
     * The test will try to match each XML file in input directory with
     * the file of the same name in the output directory.
     *
     * It is possible to test lossy conversion as well (say legacy ezxml).
     * To use this file name of the fixture that is converted with data loss
     * needs to end with `.lossy.xml`. As input test with this fixture will
     * be skipped, but as output fixture it will be matched to the input
     * fixture file of the same name but without `.lossy` part.
     *
     * Comments in fixtures are removed before conversion, so be free to use
     * comments inside fixtures for documentation as needed.
     *
     * @return array
     */
    public function getFixtureSubdirectories()
    {
        return array(
            'input' => 'docbook',
            'output' => 'xhtml5/edit',
        );
    }

    /**
     * Return the absolute path to conversion transformation stylesheet.
     *
     * @return string
     */
    protected function getConversionTransformationStylesheet()
    {
        return __DIR__ . '/../../../../RichText/Resources/stylesheets/docbook/xhtml5/edit/xhtml5.xsl';
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
                'path' => __DIR__ . '/../../../../RichText/Resources/stylesheets/docbook/xhtml5/edit/core.xsl',
                'priority' => 100,
            ),
            array(
                'path' => __DIR__ . '/_fixtures/docbook/custom_stylesheets/youtube_xhtml5_edit.xsl',
                'priority' => 99,
            ),
        );
    }
}
