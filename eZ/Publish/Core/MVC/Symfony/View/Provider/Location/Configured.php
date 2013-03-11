<?php
/**
 * File containing the View\Provider\Location\Configured class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Provider\Location;

use eZ\Publish\Core\MVC\Symfony\View\Provider\Location as LocationViewProvider;
use eZ\Publish\Core\MVC\Symfony\View\Provider\ContentBasedConfigured as ProviderConfigured;
use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher;
use InvalidArgumentException;

class Configured extends ProviderConfigured implements LocationViewProvider
{
    /**
     * Returns a ContentView object corresponding to $location, or null if not applicable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content.
     *
     * @throws \InvalidArgumentException
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|null
     */
    public function getView( Location $location, $viewType )
    {
        if ( !isset( $this->matchConfig[$viewType] ) )
            return;

        return $this->doMatch( $this->matchConfig[$viewType], $location );
    }

    /**
     * {@inheritDoc}
     */
    public function match( ViewProviderMatcher $matcher, ValueObject $valueObject )
    {
        if ( !$valueObject instanceof Location )
            throw new InvalidArgumentException( 'Value object must be a valid Location instance' );

        return $matcher->matchLocation( $valueObject );
    }
}
