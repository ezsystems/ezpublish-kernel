<?php

declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Helper;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\Repository\Values\Content\LanguageProxy;
use eZ\Publish\SPI\Persistence\Content\Language as SPILanguage;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use Generator;

/**
 * LanguageDomainMapper is an internal service.
 *
 * @internal Meant for internal use by Repository.
 */
final class LanguageDomainMapper
{
    /** @var \eZ\Publish\SPI\Persistence\Content\Language\Handler */
    private $languageHandler;

    public function __construct(LanguageHandler $languageHandler)
    {
        $this->languageHandler = $languageHandler;
    }

    /**
     * Builds Language domain object from ValueObject returned by Persistence API.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language $spiLanguage
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function buildDomainObject(SPILanguage $spiLanguage): Language
    {
        return new Language($this->getDomainObjectData($spiLanguage));
    }

    public function buildDomainObjectProxy(string $languageCode): Language
    {
        return new LanguageProxy(
            $this->getProxyInitializer(),
            $languageCode
        );
    }

    private function getProxyInitializer(): Generator
    {
        $languageCode = yield;

        yield $this->getDomainObjectData(
            $this->languageHandler->loadByLanguageCode($languageCode)
        );
    }

    private function getDomainObjectData(SPILanguage $spiLanguage): array
    {
        return [
            'id' => $spiLanguage->id,
            'languageCode' => $spiLanguage->languageCode,
            'name' => $spiLanguage->name,
            'enabled' => $spiLanguage->isEnabled,
        ];
    }
}
