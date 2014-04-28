<?php
/**
 * File containing the FeatureContext class for Legacy Bundle.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
