<?php

/**
 * File containing the Docbook validation test.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\RichText\Validator;

use eZ\Publish\Core\FieldType\RichText\Validator;
use DOMDocument;
use PHPUnit\Framework\TestCase;

class DocbookTest extends TestCase
{
    public function providerForTestValidate()
    {
        return [
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <link xlink:href="ezurl://72">Hello <link xlink:href="ezurl://27">goodbye</link></link>
    </para>
</section>
',
                [
                    'link must not occur in the descendants of link',
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>Some <link xlink:href="ezcontent://601">linked <ezembedinline xlink:href="ezcontent://601">
        <ezlink xlink:href="ezcontent://106"/>
    </ezembedinline> linked</link> embeds.</para>
</section>
',
                [
                    'ezlink must not occur in the descendants of link',
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para ezxhtml:class="listening loud indie rock">Nada Surf - Happy Kid</para>
</section>
',
                [],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para ezxhtml:class="">Nada Surf - Happy Kid</para>
</section>
',
                [],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook"
         xmlns:xlink="http://www.w3.org/1999/xlink"
         xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
         xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
         version="5.0-variant ezpublish-1.0">
  <blockquote>
    <para>Some comments to people\'s comments!</para>
  </blockquote>
  <blockquote>
    <title ezxhtml:level="3">Header level 3</title>
    <title ezxhtml:level="4">Header level 4</title>
    <para>foobar quote<link xlink:href="ezurl://1044" xlink:show="none">http://ez.no</link> for more info.</para>
  </blockquote>
</section>
',
                [],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section
    xmlns="http://docbook.org/ns/docbook"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
    xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>test
        <superscript>1
            <emphasis role="strong">bold</emphasis>
            <emphasis>italic</emphasis>
            <emphasis role="underlined">underline</emphasis> superscript
            <link xlink:href="http://ez.no" xlink:show="none" xlink:title="link tile">link</link>
            <emphasis role="strikedthrough">strikedthrough</emphasis>
        </superscript>
    </para>
</section>',
                [],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section
    xmlns="http://docbook.org/ns/docbook"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
    xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>test
        <subscript>1
            <emphasis role="strong">bold</emphasis>
            <emphasis>italic</emphasis>
            <emphasis role="underlined">underline</emphasis> subscript
            <link xlink:href="http://ez.no" xlink:show="none" xlink:title="link tile">link</link>
            <emphasis role="strikedthrough">strikedthrough</emphasis>
        </subscript>
    </para>
</section>',
                [],
            ],
        ];
    }

    /**
     * @dataProvider providerForTestValidate
     */
    public function testValidate($input, $expectedErrors)
    {
        $document = new DOMDocument();
        $document->loadXML($input);

        $validator = $this->getConversionValidator();
        $errors = $validator->validate($document);

        $this->assertEquals(count($expectedErrors), count($errors));

        foreach ($errors as $index => $error) {
            $this->assertStringEndsWith($expectedErrors[$index], $error);
        }
    }

    /** @var \eZ\Publish\Core\FieldType\RichText\Validator */
    protected $validator;

    /**
     * @return \eZ\Publish\Core\FieldType\RichText\Validator
     */
    protected function getConversionValidator()
    {
        $validationSchema = $this->getConversionValidationSchemas();
        if ($validationSchema !== null && $this->validator === null) {
            $this->validator = new Validator($validationSchema);
        }

        return $this->validator;
    }

    /**
     * Return an array of absolute paths to validation schemas.
     *
     * @return string[]
     */
    protected function getConversionValidationSchemas()
    {
        return [
            __DIR__ . '/../../../RichText/Resources/schemas/docbook/ezpublish.rng',
            __DIR__ . '/../../../RichText/Resources/schemas/docbook/docbook.iso.sch.xsl',
        ];
    }
}
