<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\URL;

use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\API\Repository\Values\URL\Query\SortClause;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\Core\Persistence\Legacy\URL\Gateway;
use eZ\Publish\Core\Persistence\Legacy\URL\Handler;
use eZ\Publish\Core\Persistence\Legacy\URL\Mapper;
use eZ\Publish\SPI\Persistence\URL\URL;
use eZ\Publish\SPI\Persistence\URL\URLUpdateStruct;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\URL\Gateway|\PHPUnit\Framework\MockObject\MockObject */
    private $gateway;

    /** @var \eZ\Publish\Core\Persistence\Legacy\URL\Mapper|\PHPUnit\Framework\MockObject\MockObject */
    private $mapper;

    /** @var \eZ\Publish\Core\Persistence\Legacy\URL\Handler */
    private $handler;

    protected function setUp()
    {
        parent::setUp();
        $this->gateway = $this->createMock(Gateway::class);
        $this->mapper = $this->createMock(Mapper::class);
        $this->handler = new Handler($this->gateway, $this->mapper);
    }

    public function testUpdateUrl()
    {
        $urlUpdateStruct = new URLUpdateStruct();
        $url = $this->getUrl(1, 'http://ez.no');

        $this->mapper
            ->expects($this->once())
            ->method('createURLFromUpdateStruct')
            ->with($urlUpdateStruct)
            ->willReturn($url);

        $this->gateway
            ->expects($this->once())
            ->method('updateUrl')
            ->with($url);

        $this->assertEquals($url, $this->handler->updateUrl($url->id, $urlUpdateStruct));
    }

    public function testFind()
    {
        $query = new URLQuery();
        $query->filter = new Criterion\Validity(true);
        $query->sortClauses = [
            new SortClause\Id(),
        ];
        $query->offset = 2;
        $query->limit = 10;

        $results = [
            'count' => 1,
            'rows' => [
                [
                    'id' => 1,
                    'url' => 'http://ez.no',
                ],
            ],
        ];

        $expected = [
            'count' => 1,
            'items' => [
                $this->getUrl(1, 'http://ez.no'),
            ],
        ];

        $this->gateway
            ->expects($this->once())
            ->method('find')
            ->with($query->filter, $query->offset, $query->limit, $query->sortClauses)
            ->willReturn($results);

        $this->mapper
            ->expects($this->once())
            ->method('extractURLsFromRows')
            ->with($results['rows'])
            ->willReturn($expected['items']);

        $this->assertEquals($expected, $this->handler->find($query));
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadByIdWithoutUrlData()
    {
        $id = 1;

        $this->gateway
            ->expects($this->once())
            ->method('loadUrlData')
            ->with($id)
            ->willReturn([]);

        $this->mapper
            ->expects($this->once())
            ->method('extractURLsFromRows')
            ->with([])
            ->willReturn([]);

        $this->handler->loadById($id);
    }

    public function testLoadByIdWithUrlData()
    {
        $url = $this->getUrl(1, 'http://ez.no');

        $this->gateway
            ->expects($this->once())
            ->method('loadUrlData')
            ->with($url->id)
            ->willReturn([$url]);

        $this->mapper
            ->expects($this->once())
            ->method('extractURLsFromRows')
            ->with([$url])
            ->willReturn([$url]);

        $this->assertEquals($url, $this->handler->loadById($url->id));
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadByUrlWithoutUrlData()
    {
        $url = 'http://ez.no';

        $this->gateway
            ->expects($this->once())
            ->method('loadUrlDataByUrl')
            ->with($url)
            ->willReturn([]);

        $this->mapper
            ->expects($this->once())
            ->method('extractURLsFromRows')
            ->with([])
            ->willReturn([]);

        $this->handler->loadByUrl($url);
    }

    public function testLoadByUrlWithUrlData()
    {
        $url = $this->getUrl(1, 'http://ez.no');

        $this->gateway
            ->expects($this->once())
            ->method('loadUrlDataByUrl')
            ->with($url->url)
            ->willReturn([$url]);

        $this->mapper
            ->expects($this->once())
            ->method('extractURLsFromRows')
            ->with([$url])
            ->willReturn([$url]);

        $this->assertEquals($url, $this->handler->loadByUrl($url->url));
    }

    public function testFindUsages()
    {
        $url = $this->getUrl();
        $ids = [1, 2, 3];

        $this->gateway
            ->expects($this->once())
            ->method('findUsages')
            ->with($url->id)
            ->will($this->returnValue($ids));

        $this->assertEquals($ids, $this->handler->findUsages($url->id));
    }

    private function getUrl($id = 1, $urlAddr = 'http://ez.no')
    {
        $url = new URL();
        $url->id = $id;
        $url->url = $url;

        return $url;
    }
}
