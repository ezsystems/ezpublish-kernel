<?php
/**
 * File containing the LegacyResponseManagerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Tests\LegacyResponse;

use eZ\Bundle\EzPublishLegacyBundle\LegacyResponse\LegacyResponseManager;
use eZ\Bundle\EzPublishLegacyBundle\LegacyResponse;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Templating\EngineInterface;
use ezpKernelResult;
use ezpKernelRedirect;
use DateTime;

class LegacyResponseManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EngineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $templateEngine;

    /**
     * @var ConfigResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    protected function setUp()
    {
        parent::setUp();
        $this->templateEngine = $this->getMock( 'Symfony\Component\Templating\EngineInterface' );
        $this->configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
    }

    /**
     * @dataProvider generateResponseAccessDeniedProvider
     */
    public function testGenerateResponseAccessDenied( $errorCode, $errorMessage )
    {
        $this->setExpectedException( 'Symfony\Component\Security\Core\Exception\AccessDeniedException', $errorMessage );
        $manager = new LegacyResponseManager( $this->templateEngine, $this->configResolver );
        $content = 'foobar';
        $moduleResult = array(
            'content' => $content,
            'errorCode' => $errorCode,
            'errorMessage' => $errorMessage
        );
        $kernelResult = new ezpKernelResult( $content, array( 'module_result' => $moduleResult ) );
        $manager->generateResponseFromModuleResult( $kernelResult );
    }

    public function generateResponseAccessDeniedProvider()
    {
        return array(
            array( '401', 'Unauthorized access' ),
            array( '403', 'Forbidden' ),
            array( '403', null ),
            array( '401', null ),
        );
    }

    /**
     * Tests response generation when no custom layout can be applied:
     *  - Custom layout provided, but in legacy mode
     *  - Custom layout provided, module_result presents a "pagelayout" entry
     *  - Legacy mode active, no custom layout
     *
     * @param string|null $customLayout Custom Twig layout being used, or null if none.
     * @param bool $legacyMode Whether legacy mode is active or not.
     * @param bool $moduleResultLayout Whether if module_result from legacy contains a "pagelayout" entry.
     *
     * @dataProvider generateResponseNoCustomLayoutProvider
     */
    public function testGenerateResponseNoCustomLayout( $customLayout, $legacyMode, $moduleResultLayout )
    {
        $this->configResolver
            ->expects( $this->any() )
            ->method( 'getParameter' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'module_default_layout', 'ezpublish_legacy', null, $customLayout ),
                        array( 'legacy_mode', null, null, $legacyMode )
                    )
                )
            );
        $this->templateEngine
            ->expects( $this->never() )
            ->method( 'render' );

        $manager = new LegacyResponseManager( $this->templateEngine, $this->configResolver );
        $content = 'foobar';
        $moduleResult = array(
            'content' => $content,
            'errorCode' => 200,
        );
        if ( $moduleResultLayout )
            $moduleResult['pagelayout'] = 'design:some_page_layout.tpl';

        $kernelResult = new ezpKernelResult( $content, array( 'module_result' => $moduleResult ) );

        $response = $manager->generateResponseFromModuleResult( $kernelResult );
        $this->assertInstanceOf( 'eZ\Bundle\EzPublishLegacyBundle\LegacyResponse', $response );
        $this->assertSame( $content, $response->getContent() );
        $this->assertSame( $moduleResult['errorCode'], $response->getStatusCode() );
    }

    public function generateResponseNoCustomLayoutProvider()
    {
        return array(
            array( null, false, false ),
            array( 'foo.html.twig', true, false ),
            array( 'foo.html.twig', false, true ),
            array( null, false, true ),
            array( null, true, true ),
        );
    }

    /**
     * @dataProvider generateResponseWithCustomLayoutProvider
     */
    public function testGenerateResponseWithCustomLayout( $customLayout, $content )
    {
        $contentWithLayout = "<div id=\"i-am-a-twig-layout\">$content</div>";
        $moduleResult = array(
            'content' => $content,
            'errorCode' => 200,
        );

        $this->configResolver
            ->expects( $this->any() )
            ->method( 'getParameter' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'module_default_layout', 'ezpublish_legacy', null, $customLayout ),
                        array( 'legacy_mode', null, null, false )
                    )
                )
            );
        $this->templateEngine
            ->expects( $this->once() )
            ->method( 'render' )
            ->with( $customLayout, array( 'module_result' => $moduleResult ) )
            ->will( $this->returnValue( $contentWithLayout ) );

        $manager = new LegacyResponseManager( $this->templateEngine, $this->configResolver );

        $kernelResult = new ezpKernelResult( $content, array( 'module_result' => $moduleResult ) );

        $response = $manager->generateResponseFromModuleResult( $kernelResult );
        $this->assertInstanceOf( 'eZ\Bundle\EzPublishLegacyBundle\LegacyResponse', $response );
        $this->assertSame( $contentWithLayout, $response->getContent() );
        $this->assertSame( $moduleResult['errorCode'], $response->getStatusCode() );
        $this->assertSame( $moduleResult, $response->getModuleResult() );
    }

    public function generateResponseWithCustomLayoutProvider()
    {
        return array(
            array( 'foo.html.twig', 'Hello world!' ),
            array( 'foo.html.twig', 'שלום עולם!' ),
            array( 'bar.html.twig', 'こんにちは、世界' ),
            array( 'i_am_a_custom_layout.html.twig', 'Know what? I\'m a legacy content!' ),
            array( 'custom.twig', 'I love content management.' ),
            array( 'custom.twig', '私は、コンテンツ管理が大好きです。' ),
            array( 'custom.twig', 'אני אוהב את ניהול תוכן.' ),
        );
    }

    /**
     * @dataProvider generateRedirectResponseProvider
     */
    public function testGenerateRedirectResponse( $uri, $redirectStatus, $expectedStatusCode, $content )
    {
        $kernelRedirect = new ezpKernelRedirect( $uri, $redirectStatus, $content );
        $manager = new LegacyResponseManager( $this->templateEngine, $this->configResolver );
        $response = $manager->generateRedirectResponse( $kernelRedirect );
        $uriInContent = htmlspecialchars( $uri );
        $expectedContent = <<<EOT
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="1;url=$uriInContent" />

        <title>Redirecting to $uriInContent</title>
    </head>
    <body>
        Redirecting to <a href="$uriInContent">$uriInContent</a>.
    </body>
</html>
EOT;

        $this->assertInstanceOf( 'Symfony\Component\HttpFoundation\RedirectResponse', $response );
        $this->assertSame( $uri, $response->getTargetUrl() );
        $this->assertSame( $expectedStatusCode, $response->getStatusCode() );
        $this->assertSame( $expectedContent, $response->getContent() );
    }

    public function generateRedirectResponseProvider()
    {
        return array(
            array( '/foo', null, 302, null ),
            array( '/foo', '302', 302, 'bar' ),
            array( '/foo/bar', '301: blablabla', 301, 'Hello world!' ),
            array( '/foo/bar?some=thing&toto=titi', '303: See other', 303, 'こんにちは、世界!' ),
        );
    }

    public function testMapHeaders()
    {
        $etag = '86fb269d190d2c85f6e0468ceca42a20';
        $date = new DateTime();
        $dateForCache = $date->format( 'D, d M Y H:i:s' ).' GMT';
        $headers = array( 'X-Foo: Bar', "Etag: $etag", "Last-Modified: $dateForCache", "Expires: $dateForCache" );

        // Partially mock the manager to simulate calls to header_remove()
        $manager = $this->getMockBuilder( 'eZ\Bundle\EzPublishLegacyBundle\LegacyResponse\LegacyResponseManager' )
            ->setConstructorArgs( array( $this->templateEngine, $this->configResolver ) )
            ->setMethods( array( 'removeHeader' ) )
            ->getMock();
        $manager
            ->expects( $this->exactly( count( $headers ) ) )
            ->method( 'removeHeader' );
        /** @var \eZ\Bundle\EzPublishLegacyBundle\LegacyResponse\LegacyResponseManager|\PHPUnit_Framework_MockObject_MockObject $manager */
        $response = new LegacyResponse();
        $responseMappedHeaders = $manager->mapHeaders( $headers, $response );
        $this->assertSame( spl_object_hash( $response ), spl_object_hash( $responseMappedHeaders ) );
        $this->assertSame( 'Bar', $responseMappedHeaders->headers->get( 'X-Foo' ) );
        $this->assertSame( '"' . $etag . '"', $responseMappedHeaders->getEtag() );
        $this->assertEquals( new DateTime( $dateForCache ), $responseMappedHeaders->getLastModified() );
        $this->assertEquals( new DateTime( $dateForCache ), $responseMappedHeaders->getExpires() );
    }
}
