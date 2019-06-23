<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlWildcard\UrlWildcardMapperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlWildcard;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Mapper;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard;

/**
 * Test case for UrlWildcard Mapper.
 */
class UrlWildcardMapperTest extends TestCase
{
    /**
     * Test for the createUrlWildcard() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Mapper::createUrlWildcard
     */
    public function testCreateUrlWildcard()
    {
        $mapper = $this->getMapper();

        $urlWildcard = $mapper->createUrlWildcard(
            'pancake/*',
            'cake/{1}',
            true
        );

        self::assertEquals(
            new UrlWildcard(
                [
                    'id' => null,
                    'sourceUrl' => '/pancake/*',
                    'destinationUrl' => '/cake/{1}',
                    'forward' => true,
                ]
            ),
            $urlWildcard
        );
    }

    /**
     * Test for the extractUrlWildcardFromRow() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Mapper::extractUrlWildcardFromRow
     */
    public function testExtractUrlWildcardFromRow()
    {
        $mapper = $this->getMapper();
        $row = [
            'id' => '42',
            'source_url' => 'faq/*',
            'destination_url' => '42',
            'type' => '1',
        ];

        $urlWildcard = $mapper->extractUrlWildcardFromRow($row);

        self::assertEquals(
            new UrlWildcard(
                [
                    'id' => 42,
                    'sourceUrl' => '/faq/*',
                    'destinationUrl' => '/42',
                    'forward' => true,
                ]
            ),
            $urlWildcard
        );
    }

    /**
     * Test for the extractUrlWildcardFromRow() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Mapper::extractUrlWildcardFromRow
     */
    public function testExtractUrlWildcardsFromRows()
    {
        $mapper = $this->getMapper();
        $rows = [
            [
                'id' => '24',
                'source_url' => 'contact-information',
                'destination_url' => 'contact',
                'type' => '2',
            ],
            [
                'id' => '42',
                'source_url' => 'faq/*',
                'destination_url' => '42',
                'type' => '1',
            ],
        ];

        $urlWildcards = $mapper->extractUrlWildcardsFromRows($rows);

        self::assertEquals(
            [
                new UrlWildcard(
                    [
                        'id' => 24,
                        'sourceUrl' => '/contact-information',
                        'destinationUrl' => '/contact',
                        'forward' => false,
                    ]
                ),
                new UrlWildcard(
                    [
                        'id' => 42,
                        'sourceUrl' => '/faq/*',
                        'destinationUrl' => '/42',
                        'forward' => true,
                    ]
                ),
            ],
            $urlWildcards
        );
    }

    /**
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Mapper
     */
    protected function getMapper()
    {
        return new Mapper();
    }
}
