<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\View\LocationValueView;
use Symfony\Component\HttpFoundation\Response;

class LocationValueViewTagger implements ResponseTagger
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger
     */
    private $locationTagger;

    public function __construct(ResponseTagger $locationTagger)
    {
        $this->locationTagger = $locationTagger;
    }

    public function tag(ResponseCacheConfigurator $configurator, Response $response, $view)
    {
        if (!$view instanceof LocationValueView || !($location = $view->getLocation()) instanceof Location) {
            return $this;
        }

        $this->locationTagger->tag($configurator, $response, $location);

        return $this;
    }
}
