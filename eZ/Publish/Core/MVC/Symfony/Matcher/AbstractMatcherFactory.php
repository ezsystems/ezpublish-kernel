<?php

/**
 * File containing the AbstractMatcherFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\RepositoryAwareInterface;
use eZ\Publish\Core\MVC\Symfony\View\View;
use SplObjectStorage;
use InvalidArgumentException;

/**
 * Base for MatcherFactory classes.
 *
 * Implementors can define MATCHER_RELATIVE_NAMESPACE constant. If so, getMatcher() will return instances of objects relative
 * to this namespace if $matcherIdentifier argument doesn't begin with a '\' (FQ class name).
 *
 * @deprecated Deprecated since 6.0, will be removed in 6.1. Use ClassNameMatcherFactory instead.
 */
abstract class AbstractMatcherFactory implements MatcherFactoryInterface
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var array */
    protected $matchConfig;

    /** @var \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface[] */
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

    public function __construct(Repository $repository, array $matchConfig = [])
    {
        @trigger_error(
            "BlockMatcherFactory is deprecated, and will be removed in ezpublish-kernel 6.1.\n" .
            'Use the ServiceAwareMatcherFactory with the relative namespace as a constructor argument instead.',
            E_USER_DEPRECATED
        );

        $this->repository = $repository;
        $this->matchConfig = $matchConfig;
        $this->matchers = [];
        $this->alreadyMatched = [];
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
     * @return \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface|\eZ\Publish\Core\MVC\Symfony\Matcher\ViewMatcherInterface
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
     * $valueObject can be for example a Location or a Content object.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     *
     * @return array|null The matched configuration as a hash, containing template or controller to use, or null if not matched.
     */
    public function match(View $view)
    {
        $viewType = $view->getViewType();

        if (!isset($this->matchConfig[$viewType])) {
            return null;
        }

        if (!isset($this->alreadyMatched[$viewType])) {
            $this->alreadyMatched[$viewType] = new SplObjectStorage();
        }

        // If we already matched, just returned the matched value.
        if (isset($this->alreadyMatched[$viewType][$view])) {
            return $this->alreadyMatched[$viewType][$view];
        }

        foreach ($this->matchConfig[$viewType] as $configHash) {
            $hasMatched = true;
            $matcher = null;
            foreach ($configHash['match'] as $matcherIdentifier => $value) {
                $matcher = $this->getMatcher($matcherIdentifier);
                $matcher->setMatchingConfig($value);
                if (!$matcher->match($view)) {
                    $hasMatched = false;
                }
            }

            if ($hasMatched) {
                return $this->alreadyMatched[$viewType][$view] = $configHash + ['matcher' => $matcher];
            }
        }

        return $this->alreadyMatched[$viewType][$view] = null;
    }

    /**
     * Checks if $valueObject matches $matcher rules.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface $matcher
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $valueObject
     *
     * @return bool
     */
    abstract protected function doMatch(MatcherInterface $matcher, View $valueObject);
}
