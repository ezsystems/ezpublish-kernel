<?php

declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Helper;

use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionLazyValue as SectionLazyValueInterface;
use eZ\Publish\Core\Repository\Values\Content\SectionLazyValue;
use eZ\Publish\Core\Repository\Values\Content\SectionProxy;
use eZ\Publish\SPI\Persistence\Content\Section as SPISection;
use Generator;

/**
 * SectionDomainMapper is an internal service.
 *
 * @internal Meant for internal use by Repository.
 */
final class SectionDomainMapper
{
    /** @var \eZ\Publish\SPI\Persistence\Content\Section\Handler */
    private $sectionHandler;

    public function __construct(SPISection\Handler $sectionHandler)
    {
        $this->sectionHandler = $sectionHandler;
    }

    /**
     * Builds API Section object from provided SPI Section object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Section $spiSection
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function buildDomainObject(SPISection $spiSection): Section
    {
        return new Section($this->getDomainObjectData($spiSection));
    }

    /**
     * Builds API Section proxy object.
     *
     * @param int $sectionId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function buildDomainObjectProxy(int $sectionId): Section
    {
        return new SectionProxy(
            $this->getProxyInitializer(),
            $sectionId
        );
    }

    private function getProxyInitializer(): Generator
    {
        $sectionId = yield;

        // TODO: Missing permissions check

        yield $this->getDomainObjectData(
            $this->sectionHandler->load($sectionId)
        );
    }

    public function buildSectionLazyValue(int $sectionId): SectionLazyValueInterface
    {
        return new SectionLazyValue(
            $sectionId,
            $this->getLazyValueInitializer(),
        );
    }

    public function getLazyValueInitializer(): Generator
    {
        $sectionId = yield;

        yield $this->buildDomainObject(
            $this->sectionHandler->load($sectionId)
        );
    }

    private function getDomainObjectData(SPISection $spiSection): array
    {
        return [
            'id' => $spiSection->id,
            'identifier' => $spiSection->identifier,
            'name' => $spiSection->name,
        ];
    }
}
