<?php
/**
 * File containing the View\Provider\Content class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\View\Provider;

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
                        $tpl->setVariable( 'object_parameters', $params["objectParameters"], 'ContentView' );
                    }
                    // Used by RichText field type
                    else if ( isset( $params['embedParams'] ) )
                    {
                        $tpl->setVariable( 'object_parameters', $params["embedParams"], 'ContentView' );
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
