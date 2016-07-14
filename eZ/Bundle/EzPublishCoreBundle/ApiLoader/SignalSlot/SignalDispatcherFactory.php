<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader\SignalSlot;

use eZ\Publish\Core\Base\Container\ApiLoader\SignalSlot\SignalDispatcherFactory as BaseSignalDispatcherFactory;

class SignalDispatcherFactory extends BaseSignalDispatcherFactory
{
    /**
     * SignalDispatcherFactory constructor.
     *
     * @param string $signalDispatcherClass
     * @param string $repositoryAlias
     * @param array $repositoriesSettings
     */
    public function __construct(
        $signalDispatcherClass,
        $repositoryAlias,
        array $repositoriesSettings
    ) {
        if ($repositoryAlias === null) {
            $aliases = array_keys($repositoriesSettings);
            $repositoryAlias = array_shift($aliases);
        }
        $searchEngineAlias = isset($repositoriesSettings[$repositoryAlias]['search']['engine']) ? $repositoriesSettings[$repositoryAlias]['search']['engine'] : [];
        parent::__construct($signalDispatcherClass, $searchEngineAlias);
    }
}
