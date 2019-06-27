<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider\YamlPolicyProvider;

class StubYamlPolicyProvider extends YamlPolicyProvider
{
    /** @var array */
    private $files;

    public function __construct(array $files)
    {
        $this->files = $files;
    }

    protected function getFiles()
    {
        return $this->files;
    }
}
