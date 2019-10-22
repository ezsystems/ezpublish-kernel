<?php

declare(strict_types=1);

namespace eZ\Publish\Core\Repository\DomainProxy;

use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Values\Content\SectionLazyValue as SectionLazyValueInterface;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Repository\Helper\SectionDomainMapper;
use eZ\Publish\Core\Repository\Values\Content\SectionLazyValue;
use eZ\Publish\Core\Repository\Values\Content\SectionProxy;
use Generator;

final class LazyValueFactory implements LazyValueFactoryInterface
{
    /** @var SectionDomainMapper */
    private $sectionDomainMapper;

    /** @var PermissionResolver */
    private $permissionResolver;

    public function createSectionLazyValue(int $sectionId): SectionLazyValueInterface
    {
        $initializer = $this->sectionDomainMapper->getLazyValueInitializer();

        return new SectionLazyValue(
            $sectionId,
            $this->getWrappedInitializer($initializer),
        );
    }

    private function getWrappedInitializer(Generator $innerInitializer, string $module, string $function): Generator
    {
        $section = $innerInitializer->current();

        if (!$this->permissionResolver->canUser('section', 'view', $section)) {
            throw new UnauthorizedException('section', 'view');
        }

        yield $section;
    }
}
