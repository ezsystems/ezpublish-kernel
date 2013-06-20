<?php
/**
 * File containing the ViewParameterProvider interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Templating;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface;

/**
 * Interface for services that provides parameters to the content view.
 *
 * @see \eZ\Bundle\EzPublishCoreBundle\EventListener\ViewTemplateListener
 */
interface ViewParameterProvider
{
    /**
     * Returns a hash of parameters to inject into the template associated to the provided $contentView.
     * Depending on the view context, location/content/block will be already set in $contentView.
     * DO NOT directly inject parameters into $contentView as parameters returned by this method will be namespaced to avoid name collisions.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface $contentView
     *
     * @return array
     */
    public function getContentViewParameters( ContentViewInterface $contentView );
}
