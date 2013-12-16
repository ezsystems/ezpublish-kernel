<?php
/**
 * File containing the PageController class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\FieldType\Page\PageService as CoreBundlePageService;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\MVC\Symfony\Controller\PageController as BasePageController;

class PageController extends BasePageController
{
    public function viewBlock( Block $block, array $params = array(), array $cacheSettings = array() )
    {
        // Inject valid items as ContentInfo objects if possible.
        if ( $this->pageService instanceof CoreBundlePageService )
        {
            $params += array(
                'valid_contentinfo_items' => $this->pageService->getValidBlockItemsAsContentInfo( $block )
            );
        }

        return parent::viewBlock( $block, $params, $cacheSettings );
    }
}
