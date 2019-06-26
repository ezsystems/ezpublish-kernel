<?php

/**
 * File containing the RichTextProcessorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor\RichTextProcessor;
use eZ\Publish\Core\FieldType\RichText\Converter;
use PHPUnit\Framework\TestCase;
use DOMDocument;

class RichTextProcessorTest extends TestCase
{
    public function testPostProcessValueHash()
    {
        $processor = $this->getProcessor();

        $outputValue = [
            'xml' => <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <title>Some text</title>
    <para>Foobar</para>
</section>
EOT
        ];
        $processedOutputValue = $outputValue;
        $processedOutputValue['xhtml5edit'] = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://ez.no/namespaces/ezpublish5/xhtml5/edit">
    <h1>Some text</h1>
    <p>Foobar</p>
</section>

EOT;

        $convertedDocument = new DOMDocument();
        $convertedDocument->loadXML($processedOutputValue['xhtml5edit']);

        $this->converter
            ->expects($this->once())
            ->method('convert')
            ->with($this->isInstanceOf('DOMDocument'))
            ->will($this->returnValue($convertedDocument));

        $this->assertEquals(
            $processedOutputValue,
            $processor->postProcessValueHash($outputValue)
        );
    }

    /** @var \eZ\Publish\Core\FieldType\RichText\Converter|\PHPUnit\Framework\MockObject\MockObject */
    protected $converter;

    /**
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\RichTextProcessor
     */
    protected function getProcessor()
    {
        $this->converter = $this->createMock(Converter::class);

        return new RichTextProcessor($this->converter);
    }
}
