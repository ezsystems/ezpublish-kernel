<?php

/**
 * File containing the EzxmlToDocbookTest conversion test.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\RichText\Converter\Xslt;

use eZ\Publish\Core\FieldType\RichText\Converter\Aggregate;
use eZ\Publish\Core\FieldType\XmlText\Converter\Expanding;
use eZ\Publish\Core\FieldType\RichText\Converter\Ezxml\ToRichTextPreNormalize;
use eZ\Publish\Core\FieldType\XmlText\Converter\EmbedLinking;
use eZ\Publish\Core\FieldType\RichText\Converter\Xslt;

/**
 * Tests conversion from legacy ezxml to docbook format.
 *
 * @deprecated since version 7.2, to be removed in 8.0. *
 */
class EzxmlToDocbookTest extends BaseTest
{
    protected function setUp()
    {
        parent::setUp();

        if (!class_exists(Expanding::class)) {
            $this->markTestSkipped('This tests requires XmlText field type');
        }
    }

    /**
     * @return \eZ\Publish\Core\FieldType\RichText\Converter\Xslt
     */
    protected function getConverter()
    {
        if ($this->converter === null) {
            $this->converter = new Aggregate(
                [
                    new ToRichTextPreNormalize(new Expanding(), new EmbedLinking()),
                    new Xslt(
                        $this->getConversionTransformationStylesheet(),
                        $this->getCustomConversionTransformationStylesheets()
                    ),
                ]
            );
        }

        return $this->converter;
    }

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
        return [
            'input' => 'ezxml',
            'output' => 'docbook',
        ];
    }

    /**
     * Return the absolute path to conversion transformation stylesheet.
     *
     * @return string
     */
    protected function getConversionTransformationStylesheet()
    {
        return __DIR__ . '/../../../../RichText/Resources/stylesheets/ezxml/docbook/docbook.xsl';
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
        return [
            [
                'path' => __DIR__ . '/../../../../RichText/Resources/stylesheets/ezxml/docbook/core.xsl',
                'priority' => 99,
            ],
            [
                'path' => __DIR__ . '/_fixtures/ezxml/custom_stylesheets/youtube_docbook.xsl',
                'priority' => 100,
            ],
        ];
    }

    /**
     * Return an array of absolute paths to conversion result validation schemas.
     *
     * @return string[]
     */
    protected function getConversionValidationSchema()
    {
        return [
            __DIR__ . '/_fixtures/docbook/custom_schemas/youtube.rng',
            __DIR__ . '/../../../../RichText/Resources/schemas/docbook/docbook.iso.sch.xsl',
        ];
    }
}
