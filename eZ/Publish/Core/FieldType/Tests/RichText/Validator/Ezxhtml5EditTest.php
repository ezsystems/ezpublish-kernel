<?php

/**
 * File containing the eZXHTML5 edit validation test.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests\RichText\Validator;

use eZ\Publish\Core\FieldType\RichText\Validator;
use DOMDocument;
use PHPUnit_Framework_TestCase;

class Ezxhtml5EditTest extends PHPUnit_Framework_TestCase
{
    public function providerForTestValidate()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://ez.no/namespaces/ezpublish5/xhtml5/edit">
  <p>Some <a href="ezcontent://601">linked <ezembedinline id="id3" href="ezcontent://601" data-ezview="embed-inline-custom" class="embedClass" data-ezalign="left">
    <ezlink href="ezcontent://106" id="id4" target="_blank" title="Link title" class="linkClass"/>
  </ezembedinline> linked</a> embeds.</p>
</section>

',
                array(
                    'ezlink must not occur in the descendants of a',
                ),
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
            __DIR__ . '/../../../RichText/Resources/schemas/ezxhtml5/edit/ezxhtml5.xsd',
            __DIR__ . '/../../../RichText/Resources/schemas/ezxhtml5/edit/ezxhtml5.iso.sch.xsl',
        );
    }
}
