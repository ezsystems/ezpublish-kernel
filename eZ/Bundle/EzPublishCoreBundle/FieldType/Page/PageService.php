<?php
/**
 * File containing the PageService class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\FieldType\Page;

use eZ\Publish\Core\FieldType\Page\PageService as BasePageService;
use eZ\Publish\Core\MVC\RepositoryAwareInterface;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId;

class PageService extends BasePageService implements RepositoryAwareInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     *
     * @return void
     */
    public function setRepository( Repository $repository )
    {
        $this->repository = $repository;
    }

    /**
     * Returns valid block items as content objects.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo[]
     */
    public function getValidBlockItemsAsContentInfo( Block $block )
    {
        $contentService = $this->repository->getContentService();
        foreach ( $this->getValidBlockItems( $block ) as $item )
        {
            $contentInfoObjects[] = $contentService->loadContentInfo( $item->contentId );
        }

        return $contentInfoObjects;
    }
}
