<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\URL;

use eZ\Publish\Core\Persistence\Legacy\URL\Mapper;
use eZ\Publish\SPI\Persistence\URL\URL;
use eZ\Publish\SPI\Persistence\URL\URLUpdateStruct;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\URL\Mapper */
    private $mapper;

    protected function setUp()
    {
        parent::setUp();
        $this->mapper = new Mapper();
    }

    public function testCreateURLFromUpdateStruct()
    {
        $urlUpdateStruct = new URLUpdateStruct();
        $urlUpdateStruct->url = 'https://ez.no';
        $urlUpdateStruct->isValid = true;
        $urlUpdateStruct->lastChecked = 0;
        $urlUpdateStruct->modified = time();

        $expected = new URL();
        $expected->url = $urlUpdateStruct->url;
        $expected->originalUrlMd5 = md5($urlUpdateStruct->url);
        $expected->isValid = $urlUpdateStruct->isValid;
        $expected->lastChecked = $urlUpdateStruct->lastChecked;
        $expected->created = 0;
        $expected->modified = $urlUpdateStruct->modified;

        $this->assertEquals($expected, $this->mapper->createURLFromUpdateStruct($urlUpdateStruct));
    }

    public function testExtractURLsFromRows()
    {
        $rows = [
            [
                'id' => 12,
                'url' => 'https://ez.no',
                'original_url_md5' => 'd74110041197e107722d8821f5f4d89c',
                'is_valid' => 0,
                'last_checked' => 0,
                'created' => 1510770207,
                'modified' => 0,
            ],
            [
                'id' => 52,
                'url' => 'https://ezplatform.com',
                'original_url_md5' => '59697373afe0a059dc424ea2fc6946d5',
                'is_valid' => 1,
                'last_checked' => 0,
                'created' => 1510770293,
                'modified' => 0,
            ],
        ];

        $urlEzNo = new URL();
        $urlEzNo->id = (int)$rows[0]['id'];
        $urlEzNo->url = $rows[0]['url'];
        $urlEzNo->originalUrlMd5 = $rows[0]['original_url_md5'];
        $urlEzNo->isValid = (bool)$rows[0]['is_valid'];
        $urlEzNo->lastChecked = (int)$rows[0]['last_checked'];
        $urlEzNo->created = (int)$rows[0]['created'];
        $urlEzNo->modified = (int)$rows[0]['modified'];

        $urlEzplatformCom = new URL();
        $urlEzplatformCom->id = (int)$rows[1]['id'];
        $urlEzplatformCom->url = $rows[1]['url'];
        $urlEzplatformCom->originalUrlMd5 = $rows[1]['original_url_md5'];
        $urlEzplatformCom->isValid = (bool)$rows[1]['is_valid'];
        $urlEzplatformCom->lastChecked = (int)$rows[1]['last_checked'];
        $urlEzplatformCom->created = (int)$rows[1]['created'];
        $urlEzplatformCom->modified = (int)$rows[1]['modified'];

        $this->assertEquals([$urlEzNo, $urlEzplatformCom], $this->mapper->extractURLsFromRows($rows));
    }
}
