<?php
/**
 * File containing the ConfiguratorFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\SetupWizard;

use Sensio\Bundle\DistributionBundle\Configurator\Configurator;
use Sensio\Bundle\DistributionBundle\Configurator\Step\DoctrineStep;
use Sensio\Bundle\DistributionBundle\Configurator\Step\SecretStep;

/**
 * Factory for DistributionBundle\Configurator\Configurator with 'secret' step
 */
class ConfiguratorFactory
{
    /**
     * Factory for DistributionBundle\Configurator\Configurator with 'secret' step
     *
     * This is kept similar to SensioDistributionBundle::boot() for compatibility
     *
     * @param string $kernelDir
     *
     * @return \Sensio\Bundle\DistributionBundle\Configurator\Configurator
     */
    public function buildWebConfigurator( $kernelDir )
    {
        $configurator = new Configurator( $kernelDir );
        $configurator->addStep( new DoctrineStep( $configurator->getParameters() ) );
        $configurator->addStep( new SecretStep( $configurator->getParameters() ) );
        return $configurator;
    }
}
