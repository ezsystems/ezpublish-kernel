<?php
/**
 * File containing the MultipleValued matcher class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher;

use eZ\Publish\Core\MVC\RepositoryAware;
use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher;

/**
 * Abstract class for basic matchers to be used with View\Provider\Content\Configured, accepting multiple values to match against.
 */
abstract class MultipleValued extends RepositoryAware implements Matcher
{
    /**
     * @var array Values to test against with isset(). Key is the actual value.
     */
    protected $values;

    /**
     * Registers the matching configuration for the matcher.
     * $matchingConfig can have single (string|int...) or multiple values (array)
     *
     * @param mixed $matchingConfig
     *
     * @throws \InvalidArgumentException Should be thrown if $matchingConfig is not valid.
     *
     * @return void
     */
    public function setMatchingConfig( $matchingConfig )
    {
        $matchingConfig = !is_array( $matchingConfig ) ? array( $matchingConfig ) : $matchingConfig;
        $this->values = array_fill_keys( $matchingConfig, true );
    }

    /**
     * Returns matcher's values
     *
     * @return array
     */
    public function getValues()
    {
        return array_keys( $this->values );
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
