<?php

namespace eZ\Publish\Core\REST\Server\Tests;

use eZ\Publish\Core\REST\Server\ResourceResolver;
use eZ\Publish\Core\REST\Common\RequestParser;
use eZ\Publish\Core\REST\Common\Exceptions;

use eZ\Publish\API\Repository\ContentTypeService;

class ResourceResolverTest extends \PHPUnit_Framework_TestCase
{

    private $resourceResolver;

    private $resolverCallableMock;

    public function setup()
    {
        $this->resolverCallableMock = $this->getMockBuilder(
            \stdClass::class            
        )->setMethods(['__invoke'])
        ->getMock();

        $this->resourceResolver = new ResourceResolver(
            new RequestParser\EzPublish(),
            [
                'typeByIdentifier' => $this->resolverCallableMock,
            ]
        );
    }

    public function testResolveMapping()
    {
        $expectedResult = new \stdClass();
        $expectedParameters = ['type' => 'article'];

        $this->resolverCallableMock->expects($this->once())
            ->method('__invoke')
            ->with($expectedParameters)
            ->will($this->returnValue($expectedResult));

        $this->assertSame(
            $expectedResult,
            $this->resourceResolver->resolve('/content/types?identifier=article')
        );
    }

    public function testResolveMappingMissing()
    {
        $this->setExpectedException(Exceptions\InvalidArgumentException::class);

        $this->resourceResolver->resolve('/content/types?remoteId=123-ABC');
    }
}
