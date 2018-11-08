<?php

declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Tests\RichText;

use DOMDocument;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\FieldType\RichText\ConverterDispatcher;
use eZ\Publish\Core\FieldType\RichText\DOMDocumentFactory;
use eZ\Publish\Core\FieldType\RichText\InputHandler;
use eZ\Publish\Core\FieldType\RichText\Normalizer;
use eZ\Publish\Core\FieldType\RichText\RelationProcessor;
use eZ\Publish\Core\FieldType\RichText\ValidatorInterface;
use PHPUnit\Framework\TestCase;

class InputHandlerTest extends TestCase
{
    /**
     * @var \eZ\Publish\Core\FieldType\RichText\DOMDocumentFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $domDocumentFactory;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\ConverterDispatcher|\PHPUnit\Framework\MockObject\MockObject
     */
    private $converter;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\Normalizer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $normalizer;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\ValidatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $schemaValidator;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\ValidatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $docbookValidator;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\RelationProcessor
     */
    private $relationProcessor;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\InputHandler|\PHPUnit\Framework\MockObject\MockObject
     */
    private $inputHandler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->domDocumentFactory = new DOMDocumentFactory();
        $this->converter = $this->createMock(ConverterDispatcher::class);
        $this->normalizer = $this->createMock(Normalizer::class);
        $this->schemaValidator = $this->createMock(ValidatorInterface::class);
        $this->docbookValidator = $this->createMock(ValidatorInterface::class);
        $this->relationProcessor = new RelationProcessor();

        $this->inputHandler = new InputHandler(
            $this->domDocumentFactory,
            $this->converter,
            $this->normalizer,
            $this->schemaValidator,
            $this->docbookValidator,
            $this->relationProcessor
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\RichText\InputHandler::fromString
     */
    public function testFromString(): void
    {
        $inputXml = '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://ez.no/namespaces/ezpublish5/xhtml5/edit">
  <p>Hello World!</p>
</section>
';

        $inputHandler = $this->getMockBuilder(InputHandler::class)
            ->setConstructorArgs([
                $this->domDocumentFactory,
                $this->converter,
                $this->normalizer,
                $this->schemaValidator,
                $this->docbookValidator,
                $this->relationProcessor,
            ])
            ->setMethods(['fromDocument'])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $this->normalizer
            ->expects($this->once())
            ->method('accept')
            ->with($inputXml)
            ->willReturn(false);

        $outputDocument = $this->createMock(DOMDocument::class);

        $inputHandler
            ->expects($this->once())
            ->method('fromDocument')
            ->willReturnCallback(function (DOMDocument $document) use ($inputXml, $outputDocument) {
                $this->assertEquals($inputXml, $document->saveXML());

                return $outputDocument;
            });

        $this->assertEquals($outputDocument, $inputHandler->fromString($inputXml));
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\RichText\InputHandler::fromDocument
     */
    public function testFromDocument(): void
    {
        $inputDocument = $this->createMock(DOMDocument::class);
        $outputDocument = $this->createMock(DOMDocument::class);

        $this->schemaValidator
            ->expects($this->once())
            ->method('validateDocument')
            ->with($inputDocument)
            ->willReturn([]);

        $this->converter
            ->expects($this->once())
            ->method('dispatch')
            ->with($inputDocument)
            ->willReturn($outputDocument);

        $this->assertEquals($outputDocument, $this->inputHandler->fromDocument($inputDocument));
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\RichText\InputHandler::fromDocument
     */
    public function testFromDocumentThrowsInvalidArgumentException(): void
    {
        $inputDocument = $this->createMock(DOMDocument::class);

        $this->schemaValidator
            ->expects($this->once())
            ->method('validateDocument')
            ->with($inputDocument)
            ->willReturn([
                'At least one error',
            ]);

        $this->converter
            ->expects($this->never())
            ->method('dispatch');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'$inputValue\' is invalid: Validation of XML content failed: At least one error');

        $this->inputHandler->fromDocument($inputDocument);
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\RichText\InputHandler::getRelations
     */
    public function testGetRelations(): void
    {
        $xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0-variant ezpublish-1.0">
    <title>Some text</title>
    <para><link xlink:href="ezlocation://72">link1</link></para>
    <para><link xlink:href="ezlocation://61">link2</link></para>
    <para><link xlink:href="ezlocation://61">link3</link></para>
    <para><link xlink:href="ezcontent://70">link4</link></para>
    <para><link xlink:href="ezcontent://75">link5</link></para>
    <para><link xlink:href="ezcontent://75">link6</link></para>
</section>
EOT;

        $document = new DOMDocument();
        $document->loadXML($xml);

        $this->assertEquals([
            Relation::LINK => [
                'locationIds' => [72, 61],
                'contentIds' => [70, 75],
            ],
            Relation::EMBED => [
                'locationIds' => [],
                'contentIds' => [],
            ],
        ], $this->inputHandler->getRelations($document));
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\RichText\InputHandler::validate
     */
    public function testValidate(): void
    {
        $document = $this->createMock(DOMDocument::class);
        $expectedErrors = [
            'Example error A',
            'Example error B',
            'Example error C',
        ];

        $this->docbookValidator
            ->expects($this->once())
            ->method('validateDocument')
            ->with($document)
            ->willReturn($expectedErrors);

        $actualErrors = $this->inputHandler->validate($document);

        $this->assertEquals($expectedErrors, $actualErrors);
    }
}
