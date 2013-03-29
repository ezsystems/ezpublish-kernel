<?php
/**
 * File containing the abstract Compound Siteaccess matcher.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilderInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;

/**
 * Base for Compound siteaccess matchers.
 * All classes extending this one must implement a NAME class constant.
 */
abstract class Compound implements CompoundInterface, URILexer
{
    /**
     * @var array Collection of rules using the Compound matcher.
     */
    protected $config;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher[]
     */
    protected $matchersMap = array();

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher[]
     */
    protected $subMatchers = array();

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilderInterface
     */
    protected $matcherBuilder;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest
     */
    protected $request;

    public function __construct( array $config )
    {
        $this->config = $config;
        $this->matchersMap = array();
    }

    public function setMatcherBuilder( MatcherBuilderInterface $matcherBuidler )
    {
        $this->matcherBuilder = $matcherBuidler;
        foreach ( $this->config as $i => $rule )
        {
            foreach ( $rule['matchers'] as $matcherClass => $matchingConfig )
            {
                $this->matchersMap[$i][$matcherClass] = $matcherBuidler->buildMatcher( $matcherClass, $matchingConfig, $this->request );
            }
        }
    }

    public function setRequest( SimplifiedRequest $request )
    {
        $this->request = $request;
    }

    public function analyseURI( $uri )
    {
        foreach ( $this->getSubMatchers() as $matcher )
        {
            if ( $matcher instanceof URILexer )
            {
                $uri = $matcher->analyseURI( $uri );
            }
        }

        return $uri;
    }

    public function analyseLink( $linkUri )
    {
        foreach ( $this->getSubMatchers() as $matcher )
        {
            if ( $matcher instanceof URILexer )
            {
                $linkUri = $matcher->analyseLink( $linkUri );
            }
        }

        return $linkUri;
    }

    /**
     * Returns all used sub-matchers.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher[]
     */
    public function getSubMatchers()
    {
        return $this->subMatchers;
    }

    /**
     * Returns the matcher's name.
     * This information will be stored in the SiteAccess object itself to quickly be able to identify the matcher type.
     *
     * @return string
     */
    public function getName()
    {
        return
           'compound:' .
           static::NAME . '(' .
           implode(
               ', ',
               array_keys( $this->getSubMatchers() )
           ) . ')';
    }
}
