<?php
/**
 * File containing the ezp\Content\Services\Content class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Services;

/**
 * Content service, used for Content operations
 *
 */
use \ezp\Base\Exception;
class Content extends \ezp\Base\AbstractService
{
    /**
     * Creates the new $content in the content repository
     *
     * @param \ezp\Content\Content $content
     * @return \ezp\Content\Content The newly created content
     * @throws Exception\Validation If a validation problem has been found for $content
     */
    public function create( \ezp\Content\Content $content )
    {
        // @todo : Do any necessary actions to insert $content in the content repository
        // go through all locations to create or update them
        return $content;
    }

    /**
     * Updates $content in the content repository
     *
     * @param \ezp\Content\Content $content
     * @return \ezp\Content\Content
     * @throws Exception\Validation If a validation problem has been found for $content
     */
    public function update( \ezp\Content\Content $content )
    {
        // @todo : Do any necessary actions to update $content in the content repository
        // go through all locations to create or update them
        return $content;
    }

    /**
     * Loads a content from its id ($contentId)
     * @param int $contentId
     * @return \ezp\Content\Content
     * @throws Exception\NotFound if content could not be found
     */
    public function load( $contentId )
    {
        $content = $this->handler->contentHandler()->load( $contentId );
        if ( !$content )
            throw new Exception\NotFound( 'Content', $contentId );
        return $content;
    }


    /**
     * Deletes a content from the repository
     *
     * @param \ezp\Content\Content $content
     */
    public function delete( \ezp\Content\Content $content )
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
     * @param \ezp\Content\Content $content
     */
    public function trash( \ezp\Content\Content $content )
    {

    }

    /**
     * Restores $content from trash
     *
     * @param \ezp\Content\Content $content
     */
    public function unTrash( \ezp\Content\Content $content )
    {

    }

    /**
     * Creates a new criteria collection object in order to query the content repository
     * @return \ezp\Content\Criteria\CriteriaCollection
     */
    public function createCriteria()
    {
        return new \ezp\Content\Criteria\CriteriaCollection();
    }
}
?>
