<?php
/**
 * File containing the View\Provider\Block\Configured class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Provider\Block;

use eZ\Publish\Core\MVC\Symfony\View\Provider\Configured as ProviderConfigured;
use eZ\Publish\Core\MVC\Symfony\View\Provider\Block as BlockProvider;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\View\BlockViewProvider\Configured\Matcher;
use InvalidArgumentException;

class Configured extends ProviderConfigured implements BlockProvider
{
    const MATCHER_RELATIVE_NAMESPACE = 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\BlockViewProvider\\Configured\\Matcher';

    /**
     * Returns a ContentView object corresponding to $block, or null if not applicable
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|null
     */
    public function getView( Block $block )
    {
        return $this->doMatch( $this->matchConfig, $block );
    }

    /**
     * Returns the matcher object.
     *
     * @param string $matcherIdentifier The matcher class.
     *                                  If it begins with a '\' it means it's a FQ class name, otherwise it is relative to
     *                                  eZ\Publish\Core\MVC\Symfony\View\BlockViewProvider\Configured\Matcher namespace.
     *
     * @throws \InvalidArgumentException
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\BlockViewProvider\Configured\Matcher
     */
    protected function getMatcher( $matcherIdentifier )
    {
        $matcher = parent::getMatcher( $matcherIdentifier );
        if ( !$matcher instanceof Matcher )
        {
            throw new InvalidArgumentException(
                'Matcher for BlockViewProvider\\Configured must implement eZ\\Publish\\Core\\MVC\\Symfony\\BlockViewProvider\\Configured\\Matcher interface.'
            );
        }

        return $matcher;
    }

    /**
     * {@inheritDoc}
     */
    public function match( ViewProviderMatcher $matcher, ValueObject $valueObject )
    {
        if ( !$valueObject instanceof Block )
            throw new InvalidArgumentException( 'Value object must be a valid Block instance' );

        return $matcher->matchBlock( $valueObject );
    }
}
