<?php
/**
 * File containing the View\Provider\Content class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\View\Provider;

use eZ\Publish\Core\FieldType\XmlText\Converter\EmbedToHtml5;
use eZ\Publish\Core\MVC\Legacy\View\Provider;
use eZ\Publish\Core\MVC\Symfony\View\Provider\Content as ContentViewProviderInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZContentObject;
use eZTemplate;
use ezpEvent;

class Content extends Provider implements ContentViewProviderInterface
{
    /**
     * Returns a ContentView object corresponding to $contentInfo, or void if not applicable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string $viewType Variation of display for your content
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
     */
    public function getView( ContentInfo $contentInfo, $viewType )
    {
        $legacyKernel = $this->getLegacyKernel();
        $legacyContentClosure = function ( array $params ) use ( $contentInfo, $viewType, $legacyKernel )
        {
            return $legacyKernel->runCallback(
                function () use ( $contentInfo, $viewType, $params )
                {
                    $tpl = eZTemplate::factory();
                    /**
                     * @var \eZObjectForwarder
                     */
                    $funcObject = $tpl->fetchFunctionObject( 'content_view_gui' );
                    if ( !$funcObject )
                    {
                        return '';
                    }

                    // Used by XmlText field type
                    if ( isset( $params['objectParameters'] ) )
                    {
                        if ( isset( $params['linkParameters'] ) && $params['linkParameters'] !== null )
                        {
                            $linkParameters = $params['linkParameters'];
                        }
                        $tpl->setVariable( 'object_parameters', $params["objectParameters"], 'ContentView' );
                    }
                    // Used by RichText field type
                    else if ( isset( $params['embedParams'] ) )
                    {
                        if ( isset( $params['embedParams']['link'] ) )
                        {
                            $linkParameters = $params['embedParams']['link'];
                        }

                        if ( isset( $params['embedParams']['config'] ) )
                        {
                            $tpl->setVariable( 'object_parameters', $params['embedParams']['config'], 'ContentView' );
                        }
                    }

                    // Convert link parameters to Legacy Stack format
                    if ( isset( $linkParameters ) )
                    {
                        $tpl->setVariable(
                            'link_parameters',
                            $this->legalizeLinkParameters( $linkParameters ),
                            'ContentView'
                        );
                    }

                    $children = array();
                    $funcObject->process(
                        $tpl, $children, 'content_view_gui', false,
                        array(
                            'content_object' => array(
                                array(
                                    eZTemplate::TYPE_ARRAY,
                                    // eZTemplate::TYPE_OBJECT does not exist because
                                    // it's not possible to create "inline" objects in
                                    // legacy template engine (ie objects are always
                                    // stored in a tpl variable).
                                    // TYPE_ARRAY is used here to allow to directly
                                    // retrieve the object without creating a variable.
                                    // (TYPE_STRING, TYPE_BOOLEAN, ... have the same
                                    // behaviour, see eZTemplate::elementValue())
                                    eZContentObject::fetch( $contentInfo->id )
                                )
                            ),
                            'view' => array(
                                array(
                                    eZTemplate::TYPE_STRING,
                                    $viewType
                                )
                            )
                        ),
                        array(), '', ''
                    );
                    if ( is_array( $children ) && isset( $children[0] ) )
                    {
                        return ezpEvent::getInstance()->filter( 'response/output', $children[0] );
                    }
                    return '';
                },
                false
            );
        };
        $this->decorator->setContentView(
            new ContentView( $legacyContentClosure )
        );
        return $this->decorator;
    }

    /**
     * Converts link parameters to Legacy Stack format
     *
     * @param array $linkParameters
     *
     * @return array
     */
    protected function legalizeLinkParameters( array $linkParameters )
    {
        $parameters = array();

        if ( isset( $linkParameters["href"] ) )
        {
            $parameters["href"] = $linkParameters["href"];
        }

        if ( isset( $linkParameters["resourceFragmentIdentifier"] ) )
        {
            $parameters["anchor_name"] = $linkParameters["resourceFragmentIdentifier"];
        }

        if ( isset( $linkParameters["class"] ) )
        {
            $parameters["class"] = $linkParameters["class"];
        }

        if ( isset( $linkParameters["id"] ) )
        {
            $parameters["xhtml:id"] = $linkParameters["id"];
        }

        if ( isset( $linkParameters["target"] ) )
        {
            $parameters["target"] = $linkParameters["target"];
        }

        if ( isset( $linkParameters["title"] ) )
        {
            $parameters["xhtml:title"] = $linkParameters["title"];
        }

        if ( $linkParameters["resourceType"] !== null )
        {
            switch ( $linkParameters["resourceType"] )
            {
                case EmbedToHtml5::LINK_RESOURCE_CONTENT:
                    $parameters["object_id"] = $linkParameters["resourceId"];
                    break;

                case EmbedToHtml5::LINK_RESOURCE_LOCATION:
                    $parameters["node_id"] = $linkParameters["resourceId"];
                    break;

                case EmbedToHtml5::LINK_RESOURCE_URL:
                    $parameters["url_id"] = $linkParameters["resourceId"];
                    break;

                default:
                    // Don't set anything by default
            }
        }

        return $parameters;
    }

    /**
     * Checks if $valueObject matches the $matcher's rules.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher $matcher
     * @param \eZ\Publish\API\Repository\Values\ValueObject $valueObject
     *
     * @throws \InvalidArgumentException If $valueObject is not of expected sub-type.
     *
     * @return bool
     */
    public function match( ViewProviderMatcher $matcher, ValueObject $valueObject )
    {
        return true;
    }
}
