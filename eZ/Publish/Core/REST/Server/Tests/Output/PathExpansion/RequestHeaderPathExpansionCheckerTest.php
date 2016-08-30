<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\PathExpansion;

use eZ\Publish\Core\REST\Server\Output\PathExpansion\RequestHeaderPathExpansionChecker;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestHeaderPathExpansionCheckerTest extends PHPUnit_Framework_TestCase
{
    public function testNoRequest()
    {
        $checker = $this->buildCheckerWithRequestStack(new RequestStack());

        self::assertFalse($checker->needsExpansion('anything'));
    }

    public function testNoHeader()
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        $checker = $this->buildCheckerWithRequestStack($requestStack);

        self::assertFalse($checker->needsExpansion('anything'));
    }

    public function testEmptyHeader()
    {
        $checker = $this->buildCheckerWithRequestStack($this->buildRequestStackWithHeader(''));

        self::assertFalse($checker->needsExpansion('anything'));
    }

    public function testSimpleExpansion()
    {
        $checker = $this->buildCheckerWithRequestStack(
            $this->buildRequestStackWithHeader('Content.MainLocation,Content.Owner')
        );
        $checker = $this->buildCheckerWithRequestStack($this->buildRequestStackWithHeader('Content.MainLocation,Content.Owner'));

        self::assertTrue($checker->needsExpansion('Content.MainLocation'));
        self::assertTrue($checker->needsExpansion('Content.Owner'));
        self::assertFalse($checker->needsExpansion('Content.SomethingElse'));
    }

    public function testChildrenExpansion()
    {
        $checker = $this->buildCheckerWithRequestStack(
            $this->buildRequestStackWithHeader('Location.Children.Location.ContentInfo')
        );

        self::assertTrue($checker->needsExpansion('Location.Children'));
        self::assertTrue($checker->needsExpansion('Location.Children.Location'));
        self::assertFalse($checker->needsExpansion('Location.urlAliases'));
    }

    private function buildRequestStackWithHeader($headerValue)
    {
        $requestStack = new RequestStack();

        $request = new Request();
        $request->headers->set('x-ez-embed-value', $headerValue);
        $requestStack->push($request);

        return $requestStack;
    }

    private function buildCheckerWithRequestStack(RequestStack $requestStack)
    {
        $checker = new RequestHeaderPathExpansionChecker();
        $checker->setRequestStack($requestStack);

        return $checker;
    }
}
