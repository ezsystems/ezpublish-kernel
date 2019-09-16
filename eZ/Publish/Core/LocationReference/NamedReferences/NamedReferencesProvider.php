<?php

declare(strict_types=1);

namespace eZ\Publish\Core\LocationReference\NamedReferences;

use eZ\Publish\Core\MVC\ConfigResolverInterface;

final class NamedReferencesProvider implements NamedReferencesProviderInterface
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function getNamedReferences(): NamedReferencesCollection
    {
        $references = $this->configResolver->getParameter('location_references');
        $references['__root'] = $this->configResolver->getParameter('content.tree_root.location_id');

        return new NamedReferencesCollection($references);
    }
}
