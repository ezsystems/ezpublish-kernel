<?php

/**
 * File containing the GlobalHelperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests;

use eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper;
use Symfony\Component\HttpFoundation\Request;
use eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

class GlobalHelperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper
     */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $locationService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelper;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerInterface');
        $this->locationService = $this->getMock('eZ\\Publish\\API\\Repository\\LocationService');
        $this->configResolver = $this->getMock('eZ\\Publish\\Core\\MVC\\ConfigResolverInterface');
        $this->router = $this->getMock('Symfony\\Component\\Routing\\RouterInterface');
        $this->translationHelper = $this->getMockBuilder('eZ\Publish\Core\Helper\TranslationHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = new GlobalHelper($this->configResolver, $this->locationService, $this->router, $this->translationHelper);
    }

    public function testGetSiteaccess()
    {
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $siteAccess = $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess');
        $request->attributes->set('siteaccess', $siteAccess);
        $this->helper->setRequestStack($requestStack);

        $this->assertSame($siteAccess, $this->helper->getSiteaccess());
    }

    public function testGetViewParameters()
    {
        $request = Request::create('/foo');
        $viewParameters = array(
            'foo' => 'bar',
            'toto' => 'tata',
            'somethingelse' => 'héhé-høhø',
        );
        $request->attributes->set('viewParameters', $viewParameters);
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->helper->setRequestStack($requestStack);

        $this->assertSame($viewParameters, $this->helper->getViewParameters());
    }

    public function testGetViewParametersString()
    {
        $request = Request::create('/foo');
        $viewParametersString = '/(foo)/bar/(toto)/tata/(somethingelse)/héhé-høhø';
        $request->attributes->set('viewParametersString', $viewParametersString);
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->helper->setRequestStack($requestStack);

        $this->assertSame($viewParametersString, $this->helper->getViewParametersString());
    }

    public function testGetRequestedUriString()
    {
        $request = Request::create('/ezdemo_site/foo/bar');
        $semanticPathinfo = '/foo/bar';
        $request->attributes->set('semanticPathinfo', $semanticPathinfo);
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->helper->setRequestStack($requestStack);

        $this->assertSame($semanticPathinfo, $this->helper->getRequestedUriString());
    }

    public function testGetSystemUriStringNoUrlAlias()
    {
        $request = Request::create('/ezdemo_site/foo/bar');
        $semanticPathinfo = '/foo/bar';
        $request->attributes->set('semanticPathinfo', $semanticPathinfo);
        $request->attributes->set('_route', 'someRouteName');
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->helper->setRequestStack($requestStack);
        $this->assertSame($semanticPathinfo, $this->helper->getSystemUriString());
    }

    public function testGetSystemUriString()
    {
        $locationId = 123;
        $viewType = 'full';
        $expectedSystemUriString = '/content/location/123/full';
        $request = Request::create('/ezdemo_site/foo/bar');
        $request->attributes->set('_route', UrlAliasRouter::URL_ALIAS_ROUTE_NAME);
        $request->attributes->set('locationId', $locationId);
        $request->attributes->set('viewType', $viewType);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with('_ezpublishLocation', array('locationId' => $locationId, 'viewType' => $viewType))
            ->will($this->returnValue($expectedSystemUriString));

        $this->helper->setRequestStack($requestStack);

        $this->assertSame($expectedSystemUriString, $this->helper->getSystemUriString());
    }

    public function testGetConfigResolver()
    {
        $this->assertSame($this->configResolver, $this->helper->getConfigResolver());
    }

    public function testGetRootLocation()
    {
        $rootLocationId = 2;
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('content.tree_root.location_id')
            ->will($this->returnValue($rootLocationId));

        $rootLocation = $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\Location')
            ->setConstructorArgs(array(array('id' => $rootLocationId)));
        $this->locationService
            ->expects($this->once())
            ->method('loadLocation')
            ->with($rootLocationId)
            ->will($this->returnValue($rootLocation));

        $this->assertSame($rootLocation, $this->helper->getRootLocation());
    }

    public function testGetTranslationSiteAccess()
    {
        $language = 'fre-FR';
        $siteaccess = 'fre';
        $this->translationHelper
            ->expects($this->once())
            ->method('getTranslationSiteAccess')
            ->with($language)
            ->will($this->returnValue($siteaccess));

        $this->assertSame($siteaccess, $this->helper->getTranslationSiteAccess($language));
    }

    public function testGetAvailableLanguages()
    {
        $languages = array('fre-FR', 'eng-GB', 'esl-ES');
        $this->translationHelper
            ->expects($this->once())
            ->method('getAvailableLanguages')
            ->will($this->returnValue($languages));

        $this->assertSame($languages, $this->helper->getAvailableLanguages());
    }
}
