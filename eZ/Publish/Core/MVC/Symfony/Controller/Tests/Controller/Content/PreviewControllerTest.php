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
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
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
    private $httpKernel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $previewHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $securityContext;

    protected function setUp()
    {
        parent::setUp();

        $this->contentService = $this->getMock( 'eZ\Publish\API\Repository\ContentService' );
        $this->httpKernel = $this->getMock( 'Symfony\Component\HttpKernel\HttpKernelInterface' );
        $this->previewHelper = $this
            ->getMockBuilder( 'eZ\Publish\Core\Helper\ContentPreviewHelper' )
            ->disableOriginalConstructor()
            ->getMock();
        $this->securityContext = $this->getMock( 'Symfony\Component\Security\Core\SecurityContextInterface' );
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testPreviewUnauthorized()
    {
        $controller = new PreviewController(
            $this->contentService,
            $this->httpKernel,
            $this->previewHelper,
            $this->securityContext
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
            $this->contentService,
            $this->httpKernel,
            $this->previewHelper,
            $this->securityContext
        );
        $contentId = 123;
        $lang = 'eng-GB';
        $versionNo = 3;
        $content = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Content' );
        $contentInfo = $this->getMockBuilder( 'eZ\Publish\API\Repository\Values\Content\ContentInfo' )
            ->setConstructorArgs( array( array( 'id' => $contentId ) ) )
            ->getMockForAbstractClass();

        $this->previewHelper
            ->expects( $this->once() )
            ->method( 'getPreviewLocation' )
            ->with( $contentId )
            ->will( $this->returnValue( $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Location' ) ) );
        $this->contentService
            ->expects( $this->once() )
            ->method( 'loadContent' )
            ->with( $contentId, array( $lang ), $versionNo )
            ->will( $this->returnValue( $content ) );
        $this->securityContext
            ->expects( $this->once() )
            ->method( 'isGranted' )
            ->with( $this->equalTo( new AuthorizationAttribute( 'content', 'versionview', array( 'valueObject' => $content ) ) ) )
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
        $location = $this->getMockBuilder( 'eZ\Publish\API\Repository\Values\Content\Location' )
            ->setConstructorArgs( array( array( 'id' => $locationId ) ) )
            ->getMockForAbstractClass();

        // Repository expectations
        $this->previewHelper
            ->expects( $this->once() )
            ->method( 'getPreviewLocation' )
            ->with( $contentId )
            ->will( $this->returnValue( $location ) );
        $this->contentService
            ->expects( $this->once() )
            ->method( 'loadContent' )
            ->with( $contentId, array( $lang ), $versionNo )
            ->will( $this->returnValue( $content ) );
        $this->securityContext
            ->expects( $this->once() )
            ->method( 'isGranted' )
            ->with( $this->equalTo( new AuthorizationAttribute( 'content', 'versionview', array( 'valueObject' => $content ) ) ) )
            ->will( $this->returnValue( true ) );

        $previewSiteAccessName = 'test';
        $previewSiteAccess = new SiteAccess( $previewSiteAccessName, 'preview' );
        $previousSiteAccessName = 'foo';
        $previousSiteAccess = new SiteAccess( $previousSiteAccessName );
        $request = $this->getMock( 'Symfony\Component\HttpFoundation\Request', array( 'duplicate' ) );

        // PreviewHelper expectations
        $this->previewHelper
            ->expects( $this->once() )
            ->method( 'changeConfigScope' )
            ->with( $previewSiteAccessName )
            ->will( $this->returnValue( $previewSiteAccess ) );
        $this->previewHelper
            ->expects( $this->once() )
            ->method( 'restoreConfigScope' );

        // Request expectations
        $duplicatedRequest = new Request();
        $duplicatedRequest->attributes->add(
            array(
                '_controller' => 'ez_content:viewLocation',
                'location' => $location,
                'viewType' => ViewManagerInterface::VIEW_TYPE_FULL,
                'layout' => true,
                'params' => array(
                    'content' => $content,
                    'location' => $location,
                    'isPreview' => true,
                    'siteaccess' => $previewSiteAccess
                )
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
            $this->contentService,
            $this->httpKernel,
            $this->previewHelper,
            $this->securityContext
        );
        $controller->setRequest( $request );
        $this->assertSame(
            $expectedResponse,
            $controller->previewContentAction( $contentId, $versionNo, $lang, $previewSiteAccessName )
        );
    }
}
