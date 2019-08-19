<?php

/**
 * File containing the PreviewControllerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller\Tests\Controller\Content;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Helper\PreviewLocationProvider;
use eZ\Publish\Core\Helper\ContentPreviewHelper;
use eZ\Publish\Core\MVC\Symfony\Controller\Content\PreviewController;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\View\CustomLocationControllerChecker;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PreviewControllerTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $contentService;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $httpKernel;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $previewHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $authorizationChecker;

    /** @var PreviewLocationProvider|\PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\MVC\Symfony\View\CustomLocationControllerChecker */
    protected $locationProvider;

    protected $controllerChecker;

    protected function setUp()
    {
        parent::setUp();

        $this->contentService = $this->createMock(ContentService::class);
        $this->httpKernel = $this->createMock(HttpKernelInterface::class);
        $this->previewHelper = $this->createMock(ContentPreviewHelper::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->locationProvider = $this->createMock(PreviewLocationProvider::class);
        $this->controllerChecker = $this->createMock(CustomLocationControllerChecker::class);
    }

    /**
     * @return PreviewController
     */
    protected function getPreviewController()
    {
        $controller = new PreviewController(
            $this->contentService,
            $this->httpKernel,
            $this->previewHelper,
            $this->authorizationChecker,
            $this->locationProvider,
            $this->controllerChecker
        );

        return $controller;
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testPreviewUnauthorized()
    {
        $controller = $this->getPreviewController();
        $contentId = 123;
        $lang = 'eng-GB';
        $versionNo = 3;
        $this->contentService
            ->expects($this->once())
            ->method('loadContent')
            ->with($contentId, [$lang], $versionNo)
            ->will($this->throwException(new UnauthorizedException('foo', 'bar')));
        $controller->previewContentAction(new Request(), $contentId, $versionNo, $lang, 'test');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testPreviewCanUserFail()
    {
        $controller = $this->getPreviewController();
        $contentId = 123;
        $lang = 'eng-GB';
        $versionNo = 3;
        $content = $this->createMock(Content::class);
        $contentInfo = $this->getMockBuilder(ContentInfo::class)
            ->setConstructorArgs([['id' => $contentId]])
            ->getMockForAbstractClass();

        $this->locationProvider
            ->expects($this->once())
            ->method('loadMainLocationByContent')
            ->with($content)
            ->will($this->returnValue($this->createMock(Location::class)));
        $this->contentService
            ->expects($this->once())
            ->method('loadContent')
            ->with($contentId, [$lang], $versionNo)
            ->will($this->returnValue($content));
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo(new AuthorizationAttribute('content', 'versionread', ['valueObject' => $content])))
            ->will($this->returnValue(false));

        $controller->previewContentAction(new Request(), $contentId, $versionNo, $lang, 'test');
    }

    public function testPreview()
    {
        $contentId = 123;
        $lang = 'eng-GB';
        $versionNo = 3;
        $locationId = 456;
        $content = $this->createMock(Content::class);
        $location = $this->getMockBuilder(Location::class)
            ->setConstructorArgs([['id' => $locationId]])
            ->getMockForAbstractClass();

        // Repository expectations
        $this->locationProvider
            ->expects($this->once())
            ->method('loadMainLocationByContent')
            ->with($content)
            ->will($this->returnValue($location));
        $this->contentService
            ->expects($this->once())
            ->method('loadContent')
            ->with($contentId, [$lang], $versionNo)
            ->will($this->returnValue($content));
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo(new AuthorizationAttribute('content', 'versionread', ['valueObject' => $content])))
            ->will($this->returnValue(true));

        $previewSiteAccessName = 'test';
        $previewSiteAccess = new SiteAccess($previewSiteAccessName, 'preview');
        $previousSiteAccessName = 'foo';
        $previousSiteAccess = new SiteAccess($previousSiteAccessName);
        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['duplicate'])
            ->getMock();

        // PreviewHelper expectations
        $this->previewHelper
            ->expects($this->exactly(2))
            ->method('setPreviewActive')
            ->will(
                $this->returnValueMap(
                    [
                        [true, null],
                        [false, null],
                    ]
                )
            );
        $this->previewHelper
            ->expects($this->once())
            ->method('setPreviewedContent')
            ->with($content);
        $this->previewHelper
            ->expects($this->once())
            ->method('setPreviewedLocation')
            ->with($location);
        $this->previewHelper
            ->expects($this->once())
            ->method('getOriginalSiteAccess')
            ->will($this->returnValue($previousSiteAccess));
        $this->previewHelper
            ->expects($this->once())
            ->method('changeConfigScope')
            ->with($previewSiteAccessName)
            ->will($this->returnValue($previewSiteAccess));
        $this->previewHelper
            ->expects($this->once())
            ->method('restoreConfigScope');

        // Request expectations
        $duplicatedRequest = $this->getDuplicatedRequest($location, $content, $previewSiteAccess);
        $request
            ->expects($this->once())
            ->method('duplicate')
            ->will($this->returnValue($duplicatedRequest));

        // Kernel expectations
        $expectedResponse = new Response();
        $this->httpKernel
            ->expects($this->once())
            ->method('handle')
            ->with($duplicatedRequest, HttpKernelInterface::SUB_REQUEST)
            ->will($this->returnValue($expectedResponse));

        $controller = $this->getPreviewController();
        $this->assertSame(
            $expectedResponse,
            $controller->previewContentAction($request, $contentId, $versionNo, $lang, $previewSiteAccessName)
        );
    }

    public function testPreviewDefaultSiteaccess()
    {
        $contentId = 123;
        $lang = 'eng-GB';
        $versionNo = 3;
        $locationId = 456;
        $content = $this->createMock(Content::class);
        $location = $this->getMockBuilder(Location::class)
            ->setConstructorArgs([['id' => $locationId]])
            ->getMockForAbstractClass();

        // Repository expectations
        $this->locationProvider
            ->expects($this->once())
            ->method('loadMainLocationByContent')
            ->with($content)
            ->will($this->returnValue($location));
        $this->contentService
            ->expects($this->once())
            ->method('loadContent')
            ->with($contentId, [$lang], $versionNo)
            ->will($this->returnValue($content));
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo(new AuthorizationAttribute('content', 'versionread', ['valueObject' => $content])))
            ->will($this->returnValue(true));

        $previousSiteAccessName = 'foo';
        $previousSiteAccess = new SiteAccess($previousSiteAccessName);
        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['duplicate'])
            ->getMock();

        $this->previewHelper
            ->expects($this->once())
            ->method('getOriginalSiteAccess')
            ->will($this->returnValue($previousSiteAccess));
        $this->previewHelper
            ->expects($this->once())
            ->method('restoreConfigScope');

        // Request expectations
        $duplicatedRequest = $this->getDuplicatedRequest($location, $content, $previousSiteAccess);
        $request
            ->expects($this->once())
            ->method('duplicate')
            ->will($this->returnValue($duplicatedRequest));

        // Kernel expectations
        $expectedResponse = new Response();
        $this->httpKernel
            ->expects($this->once())
            ->method('handle')
            ->with($duplicatedRequest, HttpKernelInterface::SUB_REQUEST)
            ->will($this->returnValue($expectedResponse));

        $controller = $this->getPreviewController();
        $this->assertSame(
            $expectedResponse,
            $controller->previewContentAction($request, $contentId, $versionNo, $lang)
        );
    }

    /**
     * @param $location
     * @param $content
     * @param $previewSiteAccess
     *
     * @return Request
     */
    protected function getDuplicatedRequest(Location $location, Content $content, SiteAccess $previewSiteAccess)
    {
        $duplicatedRequest = new Request();
        $duplicatedRequest->attributes->add(
            [
                '_controller' => 'ez_content:viewLocation',
                'location' => $location,
                'viewType' => ViewManagerInterface::VIEW_TYPE_FULL,
                'layout' => true,
                'semanticPathinfo' => '/foo/bar',
                'params' => [
                    'content' => $content,
                    'location' => $location,
                    'isPreview' => true,
                    'siteaccess' => $previewSiteAccess,
                ],
            ]
        );

        return $duplicatedRequest;
    }
}
