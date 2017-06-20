<?php

namespace eZ\Publish\Core\REST\Server\Tests;

use eZ\Publish\Core\REST\Server\ResourceResolver;
use eZ\Publish\Core\REST\Common\RequestParser;

use eZ\Publish\API\Repository\ContentTypeService;

class ResourceResolverTest extends \PHPUnit_Framework_TestCase
{

    private $resourceResolver;

    private $contentTypeServiceMock;

    public function setup()
    {
        $this->contentTypeServiceMock = $this->getMockBuilder(
            ContentTypeService::class            
        )->getMock();

        $this->resourceResolver = new ResourceResolver(
            new RequestParser\EzPublish(),
            $this->contentTypeServiceMock
        );
    }

    public function provideResolveMapping()
    {
        return [
            [
                '/content/types?identifier=article',
                'loadContentTypeByIdentifier',
                'article',
            ],
            [
                '/content/types?remoteId=ABC-123',
                'loadContentTypeByRemoteId',
                'ABC-123',
            ],
            [
                '/content/types/23',
                'loadContentType',
                '23',
            ],
        ];
    }

    /**
     * @param string $uri
     * @param string $expectedMethod
     * @param string $expectedParameter
     *
     * @dataProvider provideResolveMapping
     */
    public function testResolveMapping($uri, $expectedMethod, $expectedParameter)
    {
        $expectedReturnValue = new \stdClass();

        $this->contentTypeServiceMock->expects($this->once())
            ->method($expectedMethod)
            ->with($expectedParameter)
            ->will($this->returnValue($expectedReturnValue));

        $this->assertSame(
            $expectedReturnValue,
            $this->resourceResolver->resolveContentType($uri)
        );
    }
}
