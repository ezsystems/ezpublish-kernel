<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher;

use eZ\Publish\Core\MVC\Symfony\View\View;

/**
 * Matches a View against a set of matchers.
 */
interface ViewMatcherInterface
{
    /**
     * Registers the matching configuration for the matcher.
     * It's up to the implementor to validate $matchingConfig since it can be anything configured by the end-developer.
     *
     * @param mixed $matchingConfig
     *
     * @throws \InvalidArgumentException Should be thrown if $matchingConfig is not valid.
     */
    public function setMatchingConfig($matchingConfig);

    /**
     * Matches the $view against a set of matchers.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     *
     * @return bool
     */
    public function match(View $view);
}
