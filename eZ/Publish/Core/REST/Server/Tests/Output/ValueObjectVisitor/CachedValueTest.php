<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\CachedValue;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CachedValueTest extends ValueObjectVisitorBaseTest
{
    protected $options;

    protected $defaultOptions = [
        'content.view_cache' => true,
        'content.ttl_cache' => true,
        'content.default_ttl' => 60,
    ];

    /** @var Request */
    protected $request;

    public function setUp()
    {
        $this->request = new Request();
        $this->request->headers->set('X-User-Hash', 'blabla');
    }

    public function testVisit()
    {
        $responseMock = $this->getResponseMock();
        $responseMock->expects($this->once())->method('setPublic');
        $responseMock->expects($this->at(1))->method('setVary')->with('Accept');
        $responseMock->expects($this->once())->method('setSharedMaxAge')->with($this->defaultOptions['content.default_ttl']);
        $responseMock->expects($this->at(3))->method('setVary')->with('X-User-Hash', false);

        $result = $this->visit(new CachedValue(new stdClass()));

        self::assertNotNull($result);
    }

    public function testVisitLocationCache()
    {
        $responseMock = $this->getResponseMock();
        $responseMock->expects($this->once())->method('setPublic');
        $responseMock->expects($this->at(1))->method('setVary')->with('Accept');
        $responseMock->expects($this->once())->method('setSharedMaxAge')->with($this->defaultOptions['content.default_ttl']);
        $responseMock->expects($this->at(3))->method('setVary')->with('X-User-Hash', false);

        $result = $this->visit(new CachedValue(new stdClass(), ['locationId' => 'testLocationId']));

        self::assertNotNull($result);
    }

    public function testVisitNoUserHash()
    {
        $this->request->headers->remove('X-User-Hash');

        $responseMock = $this->getResponseMock();
        $responseMock->expects($this->once())->method('setPublic');
        // no Vary header on X-User-Hash
        $responseMock->expects($this->once())->method('setVary')->with('Accept');
        $responseMock->expects($this->once())->method('setSharedMaxAge')->with($this->defaultOptions['content.default_ttl']);

        $result = $this->visit(new CachedValue(new stdClass()));

        self::assertNotNull($result);
    }

    public function testVisitNoRequest()
    {
        $this->request = null;

        $responseMock = $this->getResponseMock();
        $responseMock->expects($this->once())->method('setPublic');
        $responseMock->expects($this->once())->method('setVary')->with('Accept');
        $responseMock->expects($this->once())->method('setSharedMaxAge')->with($this->defaultOptions['content.default_ttl']);

        $result = $this->visit(new CachedValue(new stdClass()));

        self::assertNotNull($result);
    }

    public function testVisitViewCacheDisabled()
    {
        // disable caching globally
        $this->options = array_merge($this->defaultOptions, ['content.view_cache' => false]);

        $this->getResponseMock()->expects($this->never())->method('setPublic');

        $result = $this->visit(new CachedValue(new stdClass()));

        self::assertNotNull($result);
    }

    public function testVisitCacheTTLCacheDisabled()
    {
        // disable caching globally
        $this->options = array_merge($this->defaultOptions, ['content.ttl_cache' => false]);

        $responseMock = $this->getResponseMock();
        $responseMock->expects($this->once())->method('setPublic');
        $responseMock->expects($this->once())->method('setVary')->with('Accept');
        $responseMock->expects($this->never())->method('setSharedMaxAge');

        $result = $this->visit(new CachedValue(new stdClass()));

        self::assertNotNull($result);
    }

    protected function visit($value)
    {
        $this->getVisitorMock()->expects($this->once())->method('visitValueObject')->with($value->value);

        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $value
        );

        return $generator->endDocument(null);
    }

    /**
     * Must return an instance of the tested visitor object.
     *
     * @return \eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor
     */
    protected function internalGetVisitor()
    {
        $visitor = new ValueObjectVisitor\CachedValue(
            $this->getConfigProviderMock()
        );
        $requestStack = new RequestStack();
        if ($this->request) {
            $requestStack->push($this->request);
        }
        $visitor->setRequestStack($requestStack);

        return $visitor;
    }

    /**
     * @return ConfigResolverInterface|MockObject
     */
    protected function getConfigProviderMock()
    {
        $options = $this->options ?: $this->defaultOptions;

        $mock = $this->createMock(ConfigResolverInterface::class);
        $mock
            ->expects($this->any())
            ->method('hasParameter')
            ->will(
                $this->returnCallback(
                    function ($parameterName) use ($options) {
                        return isset($options[$parameterName]);
                    }
                )
            );
        $mock
            ->expects($this->any())
            ->method('getParameter')
            ->will(
                $this->returnCallback(
                    function ($parameterName, $defaultValue) use ($options) {
                        return isset($options[$parameterName]) ? $options[$parameterName] : $defaultValue;
                    }
                )
            );

        return $mock;
    }
}
