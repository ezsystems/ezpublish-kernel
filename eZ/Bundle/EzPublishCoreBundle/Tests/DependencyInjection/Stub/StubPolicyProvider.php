<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigBuilderInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider\PolicyProviderInterface;

/**
 * For tests only!!!
 * Dummy policy provider that does return policies it's given in constructor.
 */
class StubPolicyProvider implements PolicyProviderInterface
{
    /** @var array */
    private $policies;

    public function __construct(array $policies)
    {
        $this->policies = $policies;
    }

    public function addPolicies(ConfigBuilderInterface $configBuilder)
    {
        $configBuilder->addConfig($this->policies);
    }
}
