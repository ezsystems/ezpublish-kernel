<?php
/**
 * File containing the View\Provider\Content class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\View\Provider;

use eZ\Publish\Core\MVC\Legacy\View\Provider;
use eZ\Publish\Core\MVC\Symfony\View\Provider\Content as ContentViewProviderInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZContentObject;
use eZTemplate;

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
                    $children = array();
                    $params['content_object'] = array(
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
                    );
                    $params['view'] = array(
                        array(
                            eZTemplate::TYPE_STRING,
                            $viewType
                        )
                    );
                    $funcObject->process(
                        $tpl, $children, 'content_view_gui', false,
                        $params, array(), '', ''
                    );
                    if ( is_array( $children ) && isset( $children[0] ) )
                    {
                        return $children[0];
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
}
