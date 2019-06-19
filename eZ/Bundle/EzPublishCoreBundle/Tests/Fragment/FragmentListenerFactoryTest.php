<?php

/**
 * File containing the FragmentListenerFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Fragment;

use eZ\Bundle\EzPublishCoreBundle\Fragment\FragmentListenerFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\HttpKernel\EventListener\FragmentListener;
use ReflectionObject;

class FragmentListenerFactoryTest extends TestCase
{
    /**
     * @dataProvider buildFragmentListenerProvider
     */
    public function testBuildFragmentListener($requestUri, $isFragmentCandidate)
    {
        $listenerClass = FragmentListener::class;
        $uriSigner = new UriSigner('my_precious_secret');
        $baseFragmentPath = '/_fragment';
        $request = Request::create($requestUri);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $factory = new FragmentListenerFactory();
        $factory->setRequestStack($requestStack);
        $listener = $factory->buildFragmentListener($uriSigner, $baseFragmentPath, $listenerClass);
        $this->assertInstanceOf($listenerClass, $listener);

        $refListener = new ReflectionObject($listener);
        $refFragmentPath = $refListener->getProperty('fragmentPath');
        $refFragmentPath->setAccessible(true);
        if ($isFragmentCandidate) {
            $this->assertSame($requestUri, $refFragmentPath->getValue($listener));
        } else {
            $this->assertSame($baseFragmentPath, $refFragmentPath->getValue($listener));
        }
    }

    public function buildFragmentListenerProvider()
    {
        return [
            ['/foo/bar', false],
            ['/foo', false],
            ['/_fragment', true],
            ['/my_siteaccess/_fragment', true],
            ['/foo/_fragment/something', false],
            ['/_fragment/something', false],
        ];
    }

    public function testBuildFragmentListenerNoRequest()
    {
        $factory = new FragmentListenerFactory();
        $factory->setRequestStack(new RequestStack());

        $listener = $factory->buildFragmentListener(
            new UriSigner('my_precious_secret'),
            '/_fragment',
            FragmentListener::class
        );

        $this->assertNull($listener);
    }
}
