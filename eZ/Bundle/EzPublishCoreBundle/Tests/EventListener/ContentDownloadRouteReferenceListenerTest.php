<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\ContentDownloadRouteReferenceListener;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\FieldType\BinaryFile\Value as BinaryFileValue;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\Symfony\Event\RouteReferenceGenerationEvent;
use eZ\Publish\Core\MVC\Symfony\Routing\RouteReference;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class ContentDownloadRouteReferenceListenerTest extends TestCase
{
    /** @var \eZ\Publish\Core\Helper\TranslationHelper|\PHPUnit\Framework\MockObject\MockObject */
    protected $translationHelperMock;

    public function setUp()
    {
        $this->translationHelperMock = $this->createMock(TranslationHelper::class);
    }

    public function testIgnoresOtherRoutes()
    {
        $routeReference = new RouteReference('some_route');
        $event = new RouteReferenceGenerationEvent($routeReference, new Request());
        $eventListener = $this->getListener();

        $eventListener->onRouteReferenceGeneration($event);

        self::assertEquals('some_route', $routeReference->getRoute());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionOnBadContentParameter()
    {
        $routeReference = new RouteReference(
            ContentDownloadRouteReferenceListener::ROUTE_NAME,
            [
                ContentDownloadRouteReferenceListener::OPT_CONTENT => new stdClass(),
                ContentDownloadRouteReferenceListener::OPT_FIELD_IDENTIFIER => null,
            ]
        );
        $event = new RouteReferenceGenerationEvent($routeReference, new Request());
        $eventListener = $this->getListener();

        $eventListener->onRouteReferenceGeneration($event);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionOnBadFieldIdentifier()
    {
        $content = new Content(
            [
                'internalFields' => [],
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(['mainLanguageCode' => 'eng-GB']),
                    ]
                ),
            ]
        );

        $routeReference = new RouteReference(
            ContentDownloadRouteReferenceListener::ROUTE_NAME,
            [
                ContentDownloadRouteReferenceListener::OPT_CONTENT => $content,
                ContentDownloadRouteReferenceListener::OPT_FIELD_IDENTIFIER => 'field',
            ]
        );
        $event = new RouteReferenceGenerationEvent($routeReference, new Request());
        $eventListener = $this->getListener();

        $eventListener->onRouteReferenceGeneration($event);
    }

    public function testGeneratesCorrectRouteReference()
    {
        $content = $this->getCompleteContent();

        $routeReference = new RouteReference(
            ContentDownloadRouteReferenceListener::ROUTE_NAME,
            [
                ContentDownloadRouteReferenceListener::OPT_CONTENT => $content,
                ContentDownloadRouteReferenceListener::OPT_FIELD_IDENTIFIER => 'file',
            ]
        );
        $event = new RouteReferenceGenerationEvent($routeReference, new Request());
        $eventListener = $this->getListener();

        $this
            ->translationHelperMock
            ->expects($this->once())
            ->method('getTranslatedField')
            ->will($this->returnValue($content->getField('file', 'eng-GB')));
        $eventListener->onRouteReferenceGeneration($event);

        self::assertEquals('42', $routeReference->get(ContentDownloadRouteReferenceListener::OPT_CONTENT_ID));
        self::assertEquals('file', $routeReference->get(ContentDownloadRouteReferenceListener::OPT_FIELD_IDENTIFIER));
        self::assertEquals('Test-file.pdf', $routeReference->get(ContentDownloadRouteReferenceListener::OPT_DOWNLOAD_NAME));
    }

    public function testDownloadNameOverrideWorks()
    {
        $content = $this->getCompleteContent();

        $routeReference = new RouteReference(
            ContentDownloadRouteReferenceListener::ROUTE_NAME,
            [
                ContentDownloadRouteReferenceListener::OPT_CONTENT => $content,
                ContentDownloadRouteReferenceListener::OPT_FIELD_IDENTIFIER => 'file',
                ContentDownloadRouteReferenceListener::OPT_DOWNLOAD_NAME => 'My-custom-filename.pdf',
            ]
        );
        $event = new RouteReferenceGenerationEvent($routeReference, new Request());
        $eventListener = $this->getListener();

        $eventListener->onRouteReferenceGeneration($event);

        self::assertEquals('My-custom-filename.pdf', $routeReference->get(ContentDownloadRouteReferenceListener::OPT_DOWNLOAD_NAME));
    }

    /**
     * @return \eZ\Publish\Core\Repository\Values\Content\Content
     */
    protected function getCompleteContent()
    {
        return new Content(
            [
                'internalFields' => [
                        new Field(
                            [
                                'fieldDefIdentifier' => 'file',
                                'languageCode' => 'eng-GB',
                                'value' => new BinaryFileValue(['fileName' => 'Test-file.pdf']),
                            ]
                        ),
                    ],
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(['id' => 42, 'mainLanguageCode' => 'eng-GB']),
                    ]
                ),
            ]
        );
    }

    protected function getListener()
    {
        return new ContentDownloadRouteReferenceListener($this->translationHelperMock);
    }
}
