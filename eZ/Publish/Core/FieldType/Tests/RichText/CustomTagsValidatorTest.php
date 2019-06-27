<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\RichText;

use DOMDocument;
use eZ\Publish\Core\FieldType\RichText\CustomTagsValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Test RichText CustomTagsValidator.
 *
 * @see \eZ\Publish\Core\FieldType\RichText\CustomTagsValidator
 */
class CustomTagsValidatorTest extends TestCase
{
    /** @var \eZ\Publish\Core\FieldType\RichText\CustomTagsValidator */
    private $validator;

    public function setUp()
    {
        // reuse Custom Tags configuration from common test settings
        $commonSettings = Yaml::parseFile(__DIR__ . '/../../../settings/tests/common.yml');
        $customTagsConfiguration = $commonSettings['parameters']['ezplatform.ezrichtext.custom_tags'];

        $this->validator = new CustomTagsValidator($customTagsConfiguration);
    }

    /**
     * Test validating DocBook document containing Custom Tags.
     *
     * @covers \eZ\Publish\Core\FieldType\RichText\CustomTagsValidator::validateDocument
     *
     * @dataProvider providerForTestValidateDocument
     *
     * @param \DOMDocument $document
     * @param array $expectedErrors
     */
    public function testValidateDocument(DOMDocument $document, array $expectedErrors)
    {
        self::assertEquals(
            $expectedErrors,
            $this->validator->validateDocument($document)
        );
    }

    /**
     * Data provider for testValidateDocument.
     *
     * @see testValidateDocument
     *
     * @return array
     */
    public function providerForTestValidateDocument()
    {
        return [
            [
                $this->createDocument(
                    <<<DOCBOOK
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink"
         xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
         xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
         version="5.0-variant ezpublish-1.0">
  <eztemplate name="">
  </eztemplate>
</section>
DOCBOOK
                ),
                [
                    'Missing RichText Custom Tag name',
                ],
            ],
            [
                $this->createDocument(
                    <<<DOCBOOK
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink"
         xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
         xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
         version="5.0-variant ezpublish-1.0">
  <eztemplate name="video">
    <ezcontent>Content</ezcontent>
    <ezconfig>
      <ezvalue key="">Test</ezvalue>
      <ezvalue key="title">Test</ezvalue>
      <ezvalue key="width">360</ezvalue>
    </ezconfig>
  </eztemplate>
</section>
DOCBOOK
                ),
                [
                    "Missing attribute name for RichText Custom Tag 'video'",
                ],
            ],
            [
                $this->createDocument(
                    <<<DOCBOOK
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink"
         xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
         xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
         version="5.0-variant ezpublish-1.0">
  <eztemplate name="video">
    <ezcontent>Content</ezcontent>
    <ezconfig>
        <ezvalue key="title">Test</ezvalue>
        <ezvalue key="unknown">Test</ezvalue>
        <ezvalue key="width">360</ezvalue>
    </ezconfig>
  </eztemplate>
</section>
DOCBOOK
                ),
                [
                    "Unknown attribute 'unknown' of RichText Custom Tag 'video'",
                ],
            ],
            [
                $this->createDocument(
                    <<<DOCBOOK
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink"
         xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
         xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
         version="5.0-variant ezpublish-1.0">
  <eztemplate name="video">
    <ezcontent>Content</ezcontent>
    <ezconfig>
      <ezvalue key="autoplay">false</ezvalue>
    </ezconfig>
  </eztemplate>
</section>
DOCBOOK
                ),
                [
                    "The attribute 'title' of RichText Custom Tag 'video' cannot be empty",
                    "The attribute 'width' of RichText Custom Tag 'video' cannot be empty",
                ],
            ],
            [
                $this->createDocument(
                    <<<DOCBOOK
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink"
         xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
         xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
         version="5.0-variant ezpublish-1.0">
  <eztemplate name="">
  </eztemplate>
  <eztemplate name="undefined_tag">
  </eztemplate>
  <eztemplate name="video">
    <ezcontent>Content</ezcontent>
    <ezconfig>
      <ezvalue key="">Test</ezvalue>
    </ezconfig>
  </eztemplate>
  <eztemplate name="equation">
    <ezcontent>Content</ezcontent>
    <ezconfig>
      <ezvalue key="name">Test</ezvalue>
      <ezvalue key="unknown">Test</ezvalue>
      <ezvalue key="processor">latex</ezvalue>
    </ezconfig>
  </eztemplate>
</section>
DOCBOOK
                ),
                [
                    'Missing RichText Custom Tag name',
                    "Missing attribute name for RichText Custom Tag 'video'",
                    "The attribute 'title' of RichText Custom Tag 'video' cannot be empty",
                    "The attribute 'width' of RichText Custom Tag 'video' cannot be empty",
                    "Unknown attribute 'unknown' of RichText Custom Tag 'equation'",
                ],
            ],
        ];
    }

    /**
     * Test that defined but not configured yet Custom Tag doesn't cause validation error.
     */
    public function testValidateDocumentAcceptsLegacyTags()
    {
        $document = $this->createDocument(
                <<<DOCBOOK
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink"
         xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
         xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
         version="5.0-variant ezpublish-1.0">
  <eztemplate name="undefined_tag">
    <ezcontent>Undefined</ezcontent>
    <ezconfig>
      <ezvalue key="title">Test</ezvalue>
    </ezconfig>
  </eztemplate>
</section>
DOCBOOK
        );

        self::assertEmpty($this->validator->validateDocument($document));
    }

    /**
     * @param string $source XML source
     *
     * @return \DOMDocument
     */
    protected function createDocument($source)
    {
        $document = new DOMDocument();

        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;

        $document->loadXml($source, LIBXML_NOENT);

        return $document;
    }
}
