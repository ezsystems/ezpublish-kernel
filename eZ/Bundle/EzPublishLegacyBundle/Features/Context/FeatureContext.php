<?php
/**
 * File containing the FeatureContext class for Legacy Bundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Features\Context;

use eZ\Bundle\EzPublishLegacyBundle\Features\Context\SubContexts\SetupWizard;
use EzSystems\BehatBundle\Features\Context\Browser\BrowserContext;

/**
 * FeatureContext context.
 */
class FeatureContext extends BrowserContext
{
    public function __construct( array $parameters )
    {
        parent::__construct( $parameters );

        $this->pageIdentifierMap += array(
            "setup wizard" => "/ezsetup",
        );

        // load sub contexts
        $this->useContext( 'SetupWizard', new SetupWizard() );
    }
}
