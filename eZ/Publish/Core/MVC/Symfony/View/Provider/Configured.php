<?php
/**
 * File containing the View\Provider\Content\Configured class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Provider;

use eZ\Publish\API\Repository\Repository;

abstract class Configured
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var array Matching configuration hash
     */
    protected $matchConfig;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher[]
     */
    protected $matchers;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param array $matchConfig
     */
    public function __construct( Repository $repository, array $matchConfig )
    {
        $this->repository = $repository;
        $this->matchConfig = $matchConfig;
        $this->matchers = array();
    }

    /**
     * Returns the matcher object.
     *
     * @param string $matcherIdentifier The matcher class. If it begins with a '\' it means it's a FQ class name, otherwise it is relative to this namespace.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher
     */
    protected function getMatcher( $matcherIdentifier )
    {
        if ( $matcherIdentifier[0] !== '\\' )
            $matcherIdentifier = "eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentViewProvider\\Configured\\Matcher\\$matcherIdentifier";

        return new $matcherIdentifier();
    }
}
