<?php

/**
 * File generates service container instance.
 *
 * Expects global $config to be set by caller
 *
 * @internal You can include this file to get started, but note that this is not
 *           supported way of using the eZ Publish Kernel
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
use eZ\Publish\Core\Base\ServiceContainer;

if (!isset($config)) {
    throw new \RuntimeException('$config not provided to container.php');
}

// Setup class loader
require_once $config['install_dir'] . '/vendor/autoload.php';

return new ServiceContainer(
    $config['container_builder_path'],
    $config['install_dir'],
    $config['cache_dir']
);
