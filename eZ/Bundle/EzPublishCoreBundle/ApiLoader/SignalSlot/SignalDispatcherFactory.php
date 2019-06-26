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
    /** @var string */
    private $repositoryAlias;

    /** @var array */
    private $repositoriesSettings;

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
        $this->repositoryAlias = $repositoryAlias;
        $this->repositoriesSettings = $repositoriesSettings;
        parent::__construct($signalDispatcherClass, null);
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchEngineAlias()
    {
        if ($this->repositoryAlias === null) {
            $aliases = array_keys($this->repositoriesSettings);
            $this->repositoryAlias = array_shift($aliases);
        }

        return isset($this->repositoriesSettings[$this->repositoryAlias]['search']['engine']) ? $this->repositoriesSettings[$this->repositoryAlias]['search']['engine'] : '';
    }
}
