<?php

/**
 * File containing the LanguageSwitchListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\EventListener\Tests;

use eZ\Publish\Core\MVC\Symfony\Event\RouteReferenceGenerationEvent;
use eZ\Publish\Core\MVC\Symfony\EventListener\LanguageSwitchListener;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Routing\RouteReference;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;

class LanguageSwitchListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $translationHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->translationHelper = $this->getMockBuilder('eZ\Publish\Core\Helper\TranslationHelper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array(MVCEvents::ROUTE_REFERENCE_GENERATION => 'onRouteReferenceGeneration'),
            LanguageSwitchListener::getSubscribedEvents()
        );
    }

    public function testOnRouteReferenceGenerationNoLanguage()
    {
        $this->translationHelper
            ->expects($this->never())
            ->method('getTranslationSiteAccess');

        $event = new RouteReferenceGenerationEvent(new RouteReference('foo'), new Request());
        $listener = new LanguageSwitchListener($this->translationHelper);
        $listener->onRouteReferenceGeneration($event);
    }

    public function testOnRouteReferenceGeneration()
    {
        $language = 'fre-FR';
        $routeReference = new RouteReference('foo', array('language' => $language));
        $event = new RouteReferenceGenerationEvent($routeReference, new Request());
        $expectedSiteAccess = 'phoenix_rises';
        $this->translationHelper
            ->expects($this->once())
            ->method('getTranslationSiteAccess')
            ->with($language)
            ->will($this->returnValue($expectedSiteAccess));

        $listener = new LanguageSwitchListener($this->translationHelper);
        $listener->onRouteReferenceGeneration($event);
        $this->assertFalse($routeReference->has('language'));
        $this->assertTrue($routeReference->has('siteaccess'));
        $this->assertSame($expectedSiteAccess, $routeReference->get('siteaccess'));
    }

    public function testOnRouteReferenceGenerationNoTranslationSiteAccess()
    {
        $language = 'fre-FR';
        $routeReference = new RouteReference('foo', array('language' => $language));
        $event = new RouteReferenceGenerationEvent($routeReference, new Request());
        $this->translationHelper
            ->expects($this->once())
            ->method('getTranslationSiteAccess')
            ->with($language)
            ->will($this->returnValue(null));

        $listener = new LanguageSwitchListener($this->translationHelper);
        $listener->onRouteReferenceGeneration($event);
        $this->assertFalse($routeReference->has('language'));
        $this->assertFalse($routeReference->has('siteaccess'));
    }
}
