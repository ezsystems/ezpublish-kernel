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
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <link xlink:href="ezurl://72">Hello <link xlink:href="ezurl://27">goodbye</link></link>
    </para>
</section>
',
                array(
                    'link must not occur in the descendants of link',
                ),
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>Some <link xlink:href="ezcontent://601">linked <ezembedinline xlink:href="ezcontent://601">
        <ezlink xlink:href="ezcontent://106"/>
    </ezembedinline> linked</link> embeds.</para>
</section>
',
                array(
                    'ezlink must not occur in the descendants of link',
                ),
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para ezxhtml:class="listening loud indie rock">Nada Surf - Happy Kid</para>
</section>
',
                array(),
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para ezxhtml:class="">Nada Surf - Happy Kid</para>
</section>
',
                array(),
            ),
        );
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

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\Validator
     */
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
        return array(
            __DIR__ . '/../../../RichText/Resources/schemas/docbook/ezpublish.rng',
            __DIR__ . '/../../../RichText/Resources/schemas/docbook/docbook.iso.sch.xsl',
        );
    }
}
