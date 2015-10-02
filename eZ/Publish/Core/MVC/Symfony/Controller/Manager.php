<?php

/**
 * File containing the controller Manager class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBasedMatcherFactory;
use eZ\Publish\Core\MVC\Symfony\Matcher\BlockMatcherFactory;
use eZ\Publish\Core\MVC\Symfony\View\BlockValueView;
use eZ\Publish\Core\MVC\Symfony\View\ContentValueView;
use eZ\Publish\Core\MVC\Symfony\View\LocationValueView;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class Manager implements ManagerInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBasedMatcherFactory
     */
    protected $locationMatcherFactory;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBasedMatcherFactory
     */
    protected $contentMatcherFactory;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Matcher\BlockMatcherFactory
     */
    protected $blockMatcherFactory;

    public function __construct(ContentBasedMatcherFactory $locationMatcherFactory, ContentBasedMatcherFactory $contentMatcherFactory, BlockMatcherFactory $blockMatcherFactory, LoggerInterface $logger)
    {
        $this->locationMatcherFactory = $locationMatcherFactory;
        $this->contentMatcherFactory = $contentMatcherFactory;
        $this->blockMatcherFactory = $blockMatcherFactory;
        $this->logger = $logger;
    }

    /**
     * Returns a ControllerReference object corresponding to $valueObject and $viewType.
     *
     * @param ValueObject $valueObject
     * @param string $viewType
     *
     * @throws \InvalidArgumentException
     *
     * @return \Symfony\Component\HttpKernel\Controller\ControllerReference|null
     */
    public function getControllerReference(View $view)
    {
        $matchedType = null;
        if ($view instanceof LocationValueView && !$view instanceof ContentValueView) {
            $matcherProp = 'locationMatcherFactory';
            $matchedType = 'Location';
        } elseif ($view instanceof ContentValueView) {
            $matcherProp = 'contentMatcherFactory';
            $matchedType = 'Content';
        } elseif ($view instanceof BlockValueView) {
            $matcherProp = 'blockMatcherFactory';
            $matchedType = 'Block';
        } else {
            throw new InvalidArgumentException('Unsupported View type to match against');
        }

        $configHash = $this->$matcherProp->match($view);
        if (!is_array($configHash) || !isset($configHash['controller'])) {
            return null;
        }

        $this->logger->debug("Matched custom controller '{$configHash['controller']}' for $matchedType #TODO");

        return new ControllerReference($configHash['controller']);
    }
}
