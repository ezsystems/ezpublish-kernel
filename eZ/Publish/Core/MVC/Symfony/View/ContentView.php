<?php

/**
 * File containing the ContentView class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Main object to be rendered by the View Manager when viewing a content.
 * Holds the path to the template to be rendered by the view manager and the parameters to inject in it.
 *
 * The template path can be a closure. In that case, the view manager will invoke it instead of loading a template.
 * $parameters will be passed to the callable in addition to the Content or Location object (depending on the context).
 * The prototype of the closure must be :
 * <code>
 * namespace Foo;
 * use eZ\Publish\API\Repository\Values\Content\ContentInfo;
 * use eZ\Publish\API\Repository\Values\Content\Location;
 *
 * // For a content
 * function ( ContentInfo $contentInfo, array $parameters = array() )
 * {
 *     // Do something to render
 *     // Must return a string to display
 * }
 *
 * // For a location
 * function ( Location $location, array $parameters = array() )
 * {
 *     // Do something to render
 *     // Must return a string to display
 * }
 * </code>
 */
class ContentView extends BaseView implements View, ContentValueView, LocationValueView, EmbedView, CachableView
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $content;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location|null */
    private $location;

    /** @var bool */
    private $isEmbed = false;

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    public function setContent(Content $content)
    {
        $this->content = $content;
    }

    /**
     * Returns the Content.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Location|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    protected function getInternalParameters()
    {
        $parameters = ['content' => $this->content];
        if ($this->location !== null) {
            $parameters['location'] = $this->location;
        }

        return $parameters;
    }

    /**
     * Sets the value as embed / not embed.
     *
     * @param bool $value
     */
    public function setIsEmbed($value)
    {
        $this->isEmbed = (bool)$value;
    }

    /**
     * Is the view an embed or not.
     * @return bool True if the view is an embed, false if it is not.
     */
    public function isEmbed()
    {
        return $this->isEmbed;
    }
}
