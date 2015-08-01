<?php

/**
 * File containing the Configured class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Provider;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\Matcher\MatcherFactoryInterface;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\ViewProvider;
use InvalidArgumentException;

/**
 * Configured View Provider.
 *
 * @todo What about MatcherBased instead of Configured ? It is based on a matcher factory after all.
 *       It doesn't care if it was configured or not.
 *       Could also be ConfigFactory, since it uses a factory that returns a View Configuration.
 */
class Configured implements ViewProvider
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherFactoryInterface
     */
    protected $matcherFactory;

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherFactoryInterface $matcherFactory
     */
    public function __construct(MatcherFactoryInterface $matcherFactory)
    {
        $this->matcherFactory = $matcherFactory;
    }

    /**
     * Builds a ContentView object from $viewConfig.
     *
     * @param array $viewConfig
     *
     * @throws \InvalidArgumentException
     *
     * @return ContentView
     */
    protected function buildView(array $viewConfig)
    {
        if (!isset($viewConfig['template'])) {
            throw new InvalidArgumentException('$viewConfig must contain the template identifier in order to correctly generate the View');
        }

        $view = new ContentView($viewConfig['template']);
        $view->setConfigHash($viewConfig);

        return $view;
    }

    public function getView(ValueObject $valueObject, $viewType)
    {
        $viewConfig = $this->matcherFactory->match($valueObject, $viewType);
        if (empty($viewConfig)) {
            return;
        }

        return $this->buildView($viewConfig);
    }
}
