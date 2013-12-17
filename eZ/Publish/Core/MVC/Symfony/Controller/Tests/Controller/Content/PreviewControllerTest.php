<?php
/**
 * File containing the PreviewControllerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller\Tests\Controller\Content;

use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\MVC\Symfony\Controller\Content\PreviewController;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class PreviewControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $contentService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $locationService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $httpKernel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $viewManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    protected function setUp()
    {
        parent::setUp();

        $this->contentService = $this->getMock( 'eZ\Publish\API\Repository\ContentService' );
        $this->locationService = $this->getMock( 'eZ\Publish\API\Repository\LocationService' );
        $this->repository = $this
            ->getMockBuilder( 'eZ\Publish\Core\Repository\Repository' )
            ->disableOriginalConstructor()
            ->getMock();
        $this->repository
            ->expects( $this->any() )
            ->method( 'getContentService' )
            ->will( $this->returnValue( $this->contentService ) );
        $this->repository
            ->expects( $this->any() )
            ->method( 'getLocationService' )
            ->will( $this->returnValue( $this->locationService ) );

        $this->httpKernel = $this->getMock( 'Symfony\Component\HttpKernel\HttpKernelInterface' );
        $this->viewManager = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\Controller\Tests\Stubs\ViewManager' );
        $this->configResolver = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\Configuration\VersatileScopeInterface' );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function testBadViewManager()
    {
        new PreviewController(
            $this->repository,
            $this->httpKernel,
            $this->getMock( 'eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface' ),
            $this->configResolver
        );
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testPreviewUnauthorized()
    {
        $controller = new PreviewController(
            $this->repository,
            $this->httpKernel,
            $this->viewManager,
            $this->configResolver
        );
        $contentId = 123;
        $lang = 'eng-GB';
        $versionNo = 3;
        $this->contentService
            ->expects( $this->once() )
            ->method( 'loadContent' )
            ->with( $contentId, array( $lang ), $versionNo )
            ->will( $this->throwException( new UnauthorizedException( 'foo', 'bar' ) ) );
        $controller->previewContentAction( $contentId, $versionNo, $lang, 'test' );
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testPreviewCanUserFail()
    {
        $controller = new PreviewController(
            $this->repository,
            $this->httpKernel,
            $this->viewManager,
            $this->configResolver
        );
        $contentId = 123;
        $lang = 'eng-GB';
        $versionNo = 3;
        $content = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Content' );
        $contentInfo = $this->getMockBuilder( 'eZ\Publish\API\Repository\Values\Content\ContentInfo' )
            ->setConstructorArgs( array( array( 'id' => $contentId ) ) )
            ->getMockForAbstractClass();

        $this->locationService
            ->expects( $this->never() )
            ->method( 'loadLocation' );
        $this->contentService
            ->expects( $this->once() )
            ->method( 'loadContent' )
            ->with( $contentId, array( $lang ), $versionNo )
            ->will( $this->returnValue( $content ) );
        $this->contentService
            ->expects( $this->once() )
            ->method( 'loadContentInfo' )
            ->with( $contentId )
            ->will( $this->returnValue( $contentInfo ) );
        $this->repository
            ->expects( $this->once() )
            ->method( 'canUser' )
            ->with( 'content', 'versionview', $content )
            ->will( $this->returnValue( false ) );

        $controller->previewContentAction( $contentId, $versionNo, $lang, 'test' );
    }

    public function testPreview()
    {
        $contentId = 123;
        $lang = 'eng-GB';
        $versionNo = 3;
        $locationId = 456;
        $content = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Content' );
        $contentInfo = $this->getMockBuilder( 'eZ\Publish\API\Repository\Values\Content\ContentInfo' )
            ->setConstructorArgs( array( array( 'id' => $contentId, 'mainLocationId' => $locationId ) ) )
            ->getMockForAbstractClass();
        $location = $this->getMockBuilder( 'eZ\Publish\API\Repository\Values\Content\Location' )
            ->setConstructorArgs( array( array( 'id' => $locationId ) ) )
            ->getMockForAbstractClass();

        // Repository expectations
        $this->locationService
            ->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $locationId )
            ->will( $this->returnValue( $location ) );
        $this->contentService
            ->expects( $this->once() )
            ->method( 'loadContent' )
            ->with( $contentId, array( $lang ), $versionNo )
            ->will( $this->returnValue( $content ) );
        $this->contentService
            ->expects( $this->once() )
            ->method( 'loadContentInfo' )
            ->with( $contentId )
            ->will( $this->returnValue( $contentInfo ) );
        $this->repository
            ->expects( $this->once() )
            ->method( 'canUser' )
            ->with( 'content', 'versionview', $content )
            ->will( $this->returnValue( true ) );

        $previewSiteAccessName = 'test';
        $previousSiteAccessName = 'foo';
        $previousSiteAccess = new SiteAccess( $previousSiteAccessName );
        $request = $this->getMock( 'Symfony\Component\HttpFoundation\Request', array( 'duplicate' ) );

        // ConfigResolver expectations
        $this->configResolver
            ->expects( $this->at( 0 ) )
            ->method( 'getDefaultScope' )
            ->will( $this->returnValue( $previousSiteAccessName ) );
        $this->configResolver
            ->expects( $this->at( 1 ) )
            ->method( 'setDefaultScope' )
            ->with( $previewSiteAccessName );
        $this->configResolver
            ->expects( $this->at( 2 ) )
            ->method( 'setDefaultScope' )
            ->with( $previousSiteAccessName );

        // ViewManager expectations
        $this->viewManager
            ->expects( $this->at( 0 ) )
            ->method( 'setSiteAccess' )
            ->with( $this->equalTo( new SiteAccess( $previewSiteAccessName ) ) );
        $this->viewManager
            ->expects( $this->at( 1 ) )
            ->method( 'setSiteAccess' )
            ->with( $previousSiteAccess );

        // Request expectations
        $duplicatedRequest = new Request();
        $duplicatedRequest->attributes->add(
            array(
                '_controller' => 'ez_content:viewLocation',
                'location' => $location,
                'viewType' => ViewManagerInterface::VIEW_TYPE_FULL,
                'layout' => true,
                'params' => array( 'content' => $content, 'location' => $location )
            )
        );
        $request
            ->expects( $this->once() )
            ->method( 'duplicate' )
            ->will( $this->returnValue( $duplicatedRequest ) );

        // Kernel expectations
        $expectedResponse = new Response();
        $this->httpKernel
            ->expects( $this->once() )
            ->method( 'handle' )
            ->with( $duplicatedRequest, HttpKernelInterface::SUB_REQUEST )
            ->will( $this->returnValue( $expectedResponse ) );

        $controller = new PreviewController(
            $this->repository,
            $this->httpKernel,
            $this->viewManager,
            $this->configResolver
        );
        $controller->setRequest( $request );
        $controller->setSiteAccess( $previousSiteAccess );
        $this->assertSame(
            $expectedResponse,
            $controller->previewContentAction( $contentId, $versionNo, $lang, $previewSiteAccessName )
        );
    }
}
