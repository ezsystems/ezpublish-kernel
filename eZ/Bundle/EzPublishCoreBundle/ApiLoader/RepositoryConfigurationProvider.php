<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidRepositoryException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * The repository configuration provider.
 */
class RepositoryConfigurationProvider
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var array */
    private $repositories;

    public function __construct(ConfigResolverInterface $configResolver, array $repositories)
    {
        $this->configResolver = $configResolver;
        $this->repositories = $repositories;
    }

    /**
     * @return array
     *
     * @throws \eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidRepositoryException
     */
    public function getRepositoryConfig()
    {
        // Takes configured repository as the reference, if it exists.
        // If not, the first configured repository is considered instead.
        $repositoryAlias = $this->configResolver->getParameter('repository');
        if ($repositoryAlias === null) {
            $aliases = array_keys($this->repositories);
            $repositoryAlias = array_shift($aliases);
        }

        if (empty($repositoryAlias) || !isset($this->repositories[$repositoryAlias])) {
            throw new InvalidRepositoryException(
                "Undefined repository '$repositoryAlias'. Did you forget to configure it in ezpublish_*.yml?"
            );
        }

        return ['alias' => $repositoryAlias] + $this->repositories[$repositoryAlias];
    }
}
