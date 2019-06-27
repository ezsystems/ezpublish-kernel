<?php

/**
 * File containing the abstract Compound Siteaccess matcher.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
    /** @var array Collection of rules using the Compound matcher. */
    protected $config;

    /**
     * Matchers map.
     * Consists of an array of matchers, grouped by ruleset (so array of array of matchers).
     *
     * @var array
     */
    protected $matchersMap = [];

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher[] */
    protected $subMatchers = [];

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilderInterface */
    protected $matcherBuilder;

    /** @var \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest */
    protected $request;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->matchersMap = [];
    }

    public function setMatcherBuilder(MatcherBuilderInterface $matcherBuilder)
    {
        $this->matcherBuilder = $matcherBuilder;
        foreach ($this->config as $i => $rule) {
            foreach ($rule['matchers'] as $matcherClass => $matchingConfig) {
                $this->matchersMap[$i][$matcherClass] = $matcherBuilder->buildMatcher($matcherClass, $matchingConfig, $this->request);
            }
        }
    }

    public function setRequest(SimplifiedRequest $request)
    {
        $this->request = $request;
        foreach ($this->matchersMap as $ruleset) {
            foreach ($ruleset as $matcher) {
                $matcher->setRequest($request);
            }
        }
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function analyseURI($uri)
    {
        foreach ($this->getSubMatchers() as $matcher) {
            if ($matcher instanceof URILexer) {
                $uri = $matcher->analyseURI($uri);
            }
        }

        return $uri;
    }

    public function analyseLink($linkUri)
    {
        foreach ($this->getSubMatchers() as $matcher) {
            if ($matcher instanceof URILexer) {
                $linkUri = $matcher->analyseLink($linkUri);
            }
        }

        return $linkUri;
    }

    public function getSubMatchers()
    {
        return $this->subMatchers;
    }

    public function setSubMatchers(array $subMatchers)
    {
        $this->subMatchers = $subMatchers;
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
               array_keys($this->getSubMatchers())
           ) . ')';
    }

    /**
     * Serialization occurs when serializing the siteaccess for subrequests.
     *
     * @see \eZ\Bundle\EzPublishCoreBundle\Fragment\FragmentUriGenerator::generateFragmentUri()
     */
    public function __sleep()
    {
        // We don't need the whole matcher map and the matcher builder once serialized.
        // config property is not needed either as it's only needed for matching.
        return ['subMatchers'];
    }
}
