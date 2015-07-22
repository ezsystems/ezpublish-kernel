<?php

/**
 * File containing the AbstractMatcherFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Matcher;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\RepositoryAwareInterface;
use SplObjectStorage;
use InvalidArgumentException;

/**
 * Base for MatcherFactory classes.
 *
 * Implementors can define MATCHER_RELATIVE_NAMESPACE constant. If so, getMatcher() will return instances of objects relative
 * to this namespace if $matcherIdentifier argument doesn't begin with a '\' (FQ class name).
 */
abstract class AbstractMatcherFactory implements MatcherFactoryInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $matchConfig;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface[]
     */
    protected $matchers;

    /**
     * Namespace built-in matchers are relative to.
     *
     * @var string
     */
    protected $matcherRelativeNamespace;

    /**
     * Already matched value objects with their config hash.
     * Key is the view type.
     *
     * @var \SplObjectStorage[]
     */
    protected $alreadyMatched;

    public function __construct(Repository $repository, array $matchConfig)
    {
        $this->repository = $repository;
        $this->matchConfig = $matchConfig;
        $this->matchers = array();
        $this->alreadyMatched = array();
    }

    /**
     * Returns the matcher object.
     *
     * @param string $matcherIdentifier The matcher class.
     *                                  If it begins with a '\' it means it's a FQ class name, otherwise it is relative to
     *                                  static::MATCHER_RELATIVE_NAMESPACE namespace (if available).
     *
     * @throws InvalidArgumentException
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface
     */
    protected function getMatcher($matcherIdentifier)
    {
        // Not a FQ class name, so take the relative namespace.
        if ($matcherIdentifier[0] !== '\\' && defined('static::MATCHER_RELATIVE_NAMESPACE')) {
            $matcherIdentifier = static::MATCHER_RELATIVE_NAMESPACE . "\\$matcherIdentifier";
        }

        // Retrieving the matcher instance from in-memory cache
        if (isset($this->matchers[$matcherIdentifier])) {
            return $this->matchers[$matcherIdentifier];
        }

        if (!class_exists($matcherIdentifier)) {
            throw new InvalidArgumentException("Invalid matcher class '$matcherIdentifier'");
        }
        $this->matchers[$matcherIdentifier] = new $matcherIdentifier();

        if ($this->matchers[$matcherIdentifier] instanceof RepositoryAwareInterface) {
            $this->matchers[$matcherIdentifier]->setRepository($this->repository);
        }

        return $this->matchers[$matcherIdentifier];
    }

    /**
     * Checks if $valueObject has a usable configuration for $viewType.
     * If so, the configuration hash will be returned.
     *
     * $valueObject can be for example a Location or a Content object.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $valueObject
     * @param string $viewType
     *
     * @return array|null The matched configuration as a hash, containing template or controller to use, or null if not matched.
     */
    public function match(ValueObject $valueObject, $viewType)
    {
        if (!isset($this->matchConfig[$viewType])) {
            return;
        }

        if (!isset($this->alreadyMatched[$viewType])) {
            $this->alreadyMatched[$viewType] = new SplObjectStorage();
        }

        // If we already matched, just returned the matched value.
        if (isset($this->alreadyMatched[$viewType][$valueObject])) {
            return $this->alreadyMatched[$viewType][$valueObject];
        }

        foreach ($this->matchConfig[$viewType] as $configHash) {
            $hasMatched = true;
            $matcher = null;
            foreach ($configHash['match'] as $matcherIdentifier => $value) {
                $matcher = $this->getMatcher($matcherIdentifier);
                $matcher->setMatchingConfig($value);
                if (!$this->doMatch($matcher, $valueObject)) {
                    $hasMatched = false;
                }
            }

            if ($hasMatched) {
                return $this->alreadyMatched[$viewType][$valueObject] = $configHash + array('matcher' => $matcher);
            }
        }

        return $this->alreadyMatched[$viewType][$valueObject] = null;
    }

    /**
     * Checks if $valueObject matches $matcher rules.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface $matcher
     * @param ValueObject $valueObject
     *
     * @return bool
     */
    abstract protected function doMatch(MatcherInterface $matcher, ValueObject $valueObject);
}
