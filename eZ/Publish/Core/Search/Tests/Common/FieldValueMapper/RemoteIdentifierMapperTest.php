<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Search\Tests\Common\FieldValueMapper;

use eZ\Publish\Core\Search\Common\FieldValueMapper\RemoteIdentifierMapper;
use eZ\Publish\Core\Search\Tests\TestCase;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType\IdentifierField;
use eZ\Publish\SPI\Search\FieldType\IntegerField;
use eZ\Publish\SPI\Search\FieldType\RemoteIdentifierField;
use eZ\Publish\SPI\Search\FieldType\StringField;

final class RemoteIdentifierMapperTest extends TestCase
{
    /** @var \eZ\Publish\Core\Search\Common\FieldValueMapper\RemoteIdentifierMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->mapper = new RemoteIdentifierMapper();
    }

    /**
     * @covers \eZ\Publish\Core\Search\Common\FieldValueMapper\RemoteIdentifierMapper::canMap
     *
     * @dataProvider getDataForTestCanMap
     */
    public function testCanMap(Field $field, bool $canMap): void
    {
        self::assertSame($canMap, $this->mapper->canMap($field));
    }

    public function getDataForTestCanMap(): iterable
    {
        yield 'can map' => [
            new Field('id', 1, new RemoteIdentifierField()),
            true,
        ];

        yield 'cannot map (identifier)' => [
            new Field('id', 1, new IdentifierField()),
            false,
        ];

        yield 'cannot map (string)' => [
            new Field('name', 1, new StringField()),
            false,
        ];

        yield 'cannot map (integer)' => [
            new Field('number', 1, new IntegerField()),
            false,
        ];
    }

    /**
     * @covers \eZ\Publish\Core\Search\Common\FieldValueMapper\IdentifierMapper::map
     *
     * @dataProvider getDataForTestMap
     */
    public function testMap(Field $field, string $expectedMappedValue): void
    {
        self::assertSame($expectedMappedValue, $this->mapper->map($field));
    }

    public function getDataForTestMap(): iterable
    {
        yield 'numeric id' => [
            new Field('id', 1, new IdentifierField()),
            1,
        ];

        yield 'remote_id md5' => [
            new Field(
                'remote_id',
                '1611729231d469e6b53c431f476926ac',
                new IdentifierField()
            ),
            '1611729231d469e6b53c431f476926ac',
        ];

        yield 'external remote_id' => [
            new Field(
                'location_remote_id',
                'external:10',
                new IdentifierField()
            ),
            'external:10',
        ];

        yield 'section identifier' => [
            new Field(
                'section_identifier',
                'my_section',
                new IdentifierField()
            ),
            'my_section',
        ];

        yield 'path string' => [
            new Field(
                'path_string',
                '/1/2/54/',
                new IdentifierField()
            ),
            '/1/2/54/',
        ];

        yield 'identifier with national characters' => [
            new Field(
                'some_identifier',
                'zażółć gęślą jaźń',
                new IdentifierField()
            ),
            'zażółć gęślą jaźń',
        ];

        yield 'identifier with non-printable characters' => [
            new Field(
                'identifier',
                utf8_decode("Non\x09Printable\x0EIdentifier"),
                new IdentifierField()
            ),
            'Non PrintableIdentifier',
        ];

        $value = 'Emoji: ' . json_decode('"\ud83d\ude00"');
        yield 'identifier with emojis' => [
            new Field(
                'identifier',
                $value,
                new IdentifierField()
            ),
            $value,
        ];
    }
}
