<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacySearchEngineBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Publish\Core\Base\Container\Compiler\Search\Legacy\CriteriaConverterPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\Legacy\CriterionFieldValueHandlerRegistryPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\Legacy\SortClauseConverterPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\FieldRegistryPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\SearchEngineSignalSlotPass;

class EzPublishLegacySearchEngineBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CriteriaConverterPass());
        $container->addCompilerPass(new CriterionFieldValueHandlerRegistryPass());
        $container->addCompilerPass(new SortClauseConverterPass());
        $container->addCompilerPass(new FieldRegistryPass());
        $container->addCompilerPass(new SearchEngineSignalSlotPass('legacy'));
    }

    public function getContainerExtension()
    {
        if (!isset($this->extension)) {
            $this->extension = new DependencyInjection\EzPublishLegacySearchEngineExtension();
        }

        return $this->extension;
    }
}
