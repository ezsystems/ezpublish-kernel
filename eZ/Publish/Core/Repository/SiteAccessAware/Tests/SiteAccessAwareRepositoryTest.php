<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\Core\Repository\SiteAccessAware\Helper\LanguageResolver;
use eZ\Publish\Core\Repository\SiteAccessAware\Helper\DomainMapper;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Handler;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as ContentLanguageHandler;

use PHPUnit_Framework_TestCase;

abstract class SiteAccessAwareRepositoryTest extends PHPUnit_Framework_TestCase
{
    const SERVICE_NAMESPACE = 'eZ\\Publish\\Core\\Repository\\SiteAccessAware';

    /**
     * @var DomainMapper
     */
    protected $domainMapper;

    protected $languages = [
        'eng-GB',
        'fre-FR',
        'ger-DE',
    ];

    protected function getData()
    {
        return [
            // [ $contentId, $versionNo, $mainLanguageCode, $languageCodes[], $names[], $fields[][] ]
        ];
    }

    public function getService($name)
    {
        $serviceName = self::SERVICE_NAMESPACE . "\\{$name}Service";
        $serviceMockName = "eZ\\Publish\\API\\Repository\\{$name}Service";

        $serviceMock = $this->getMock($serviceMockName);
        $languageResolver = new LanguageResolver($this->languages);

        return new $serviceName($serviceMock, $languageResolver);
    }

    public function setUp()
    {
        $this->domainMapper = new DomainMapper(
            $this->getContentHandlerMock(),
            $this->getContentLanguageHandlerMock()
        );
    }

    protected function getHandlerMock()
    {
        $handlerMock = $this->getMock(Handler::class);

        $handlerMock
            ->expects($this->any())
            ->method('contentHandler')
            ->willReturn($this->getContentHandlerMock());

        $handlerMock
            ->expects($this->any())
            ->method('contentLanguageHandler')
            ->willReturn($this->getContentLanguageHandlerMock());

        return $handlerMock;
    }

    protected function getContentHandlerMock()
    {
        $contentHandlerMock = $this->getMock(ContentHandler::class);

        $contentHandlerLoadVersionInfoValueMap = [];

        foreach ($this->getData() as $data) {
            $contentHandlerLoadVersionInfoValueMap[] = [$data[0], $data[1], new VersionInfo([
                'names' => $data[4],
                'initialLanguageCode' => $data[2],
                'languageIds' => array_map(function($language) {
                    return array_search($language, $this->languages);
                }, $data[3]),
                'contentInfo' => new ContentInfo([
                    'id' => $data[0],
                    'mainLanguageCode' => $data[2],
                    'currentVersionNo' => $data[1],
                    'name' => $data[4][$data[2]],
                ]),
            ])];
        }

        $contentHandlerMock
            ->expects($this->any())
            ->method('loadVersionInfo')
            ->will($this->returnValueMap($contentHandlerLoadVersionInfoValueMap));

        return $contentHandlerMock;
    }

    protected function getContentLanguageHandlerMock()
    {
        $contentLanguageHandlerMock = $this->getMock(ContentLanguageHandler::class);

        $contentLanguageHandlerLoadValueMap = [];

        foreach ($this->languages as $id => $language) {
            $contentLanguageHandlerLoadValueMap[] = [$id, new Language([
                'languageCode' => $language
            ])];
        }

        $contentLanguageHandlerMock
            ->expects($this->any())
            ->method('load')
            ->will($this->returnValueMap($contentLanguageHandlerLoadValueMap));

        return $contentLanguageHandlerMock;
    }
}