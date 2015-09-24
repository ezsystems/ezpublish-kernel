<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider;

class RepositoryPolicyProvider extends YamlPolicyProvider
{
    public function getFiles()
    {
        return [__DIR__ . '/../../../Resources/config/policies.yml'];
    }
}
