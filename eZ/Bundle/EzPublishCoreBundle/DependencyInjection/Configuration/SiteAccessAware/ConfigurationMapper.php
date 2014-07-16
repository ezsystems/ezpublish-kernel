<?php
/**
 * File containing the ScopeConfigurationMapper interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;

interface ConfigurationMapper
{
    public function mapConfig( array $scopeSettings, $currentScope, ContextualizerInterface $contextualizer );
}
