<?php
/**
 * File containing the ezp\Content\Service class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Service as BaseService,
    ezp\Base\Exception\NotFound,
    ezp\Content,
    ezp\Content\Query;
    ezp\Content\Query\Builder;

/**
 * Content service, used for Content operations
 *
 */
class Service extends BaseService
{
    /**
     * Creates the new $content in the content repository
     *
     * @param Content $content
     * @return Content The newly created content
     * @throws Exception\Validation If a validation problem has been found for $content
     */
    public function create( Content $content )
    {
        // @todo : Do any necessary actions to insert $content in the content repository
        // go through all locations to create or update them
        return $content;
    }

    /**
     * Updates $content in the content repository
     *
     * @param Content $content
     * @return Content
     * @throws Exception\Validation If a validation problem has been found for $content
     */
    public function update( Content $content )
    {
        // @todo : Do any necessary actions to update $content in the content repository
        // go through all locations to create or update them
        return $content;
    }

    /**
     * Loads a content from its id ($contentId)
     * @param int $contentId
     * @return Content
     * @throws NotFound if content could not be found
     */
    public function load( $contentId )
    {
        $content = $this->handler->contentHandler()->load( $contentId );
        if ( !$content )
            throw new NotFound( 'Content', $contentId );
        return $content;
    }

    /**
     * Finds content using a $query
     * @param Query $query
     * @return Content[]
     */
    public function find( Query $query )
    {
        return $this->handler->contentHandler()->find( $query->criteria );
    }

    /**
     * Deletes a content from the repository
     *
     * @param Content $content
     */
    public function delete( Content $content )
    {
        // take care of:
        // 1. removing the subtree of all content's locations
        // 2. removing the content it self (with version, translations, fields
        // and so on...)
        // note: this is different from Subtree::delete()
    }

    /**
     * Sends $content to trash
     *
     * @param Content $content
     */
    public function trash( Content $content )
    {

    }

    /**
     * Restores $content from trash
     *
     * @param Content $content
     */
    public function unTrash( Content $content )
    {

    }

    /**
     * Creates a new criteria collection object in order to query the content repository
     * @return CriteriaCollection
     */
    public function getQueryBuilder()
    {
        return new Builder();
    }
}
?>
