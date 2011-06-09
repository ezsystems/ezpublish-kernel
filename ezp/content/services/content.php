<?php
/**
 * File containing the ezp\Content\Services\Content class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package Content
 * @subpackages Services
 */

/**
 * Subtree service, used for complex subtree operations
 * @package Content
 * @subpackage Services
 */
namespace ezp\Content\Services;
use ezp\Content\Content;

use ezp\Content\Repository as ContentRepository;

class Content implements ServiceInterface
{
    /**
     * Creates the new $content in the content repository under $parentLocation
     *
     * @param \ezp\Content\Content $content
     * @param \ezp\Content\Location $parentLocation
     *
     * @return \ezp\Content\Content The newly created content
     * @throws \ezp\Content\ValidationException If a validation problem has been found for $content
     */
    public function create( \ezp\Content\Content $content, \ezp\Content\Location $parentLocation )
    {
        // @todo : Do any necessary actions to insert $content in the content repository
        return $content;
    }

    /**
     * Updates $content in the content repository
     * @param \ezp\Content\Content $content
     * @return $content
     * @throws \ezp\Content\ValidationException If a validation problem has been found for $content
     */
    public function update( \ezp\Content\Content $content )
    {
        // @todo : Do any necessary actions to update $content in the content repository
        return $content;
    }

    /**
     * Loads a content from its id ($contentId)
     * @param integer $contentId
     * @return \ezp\Content\Content
     * @throws \ezp\Content\ContentNotFoundException if content could not be found
     */
    public function loadContent( $contentId )
    {

    }
}
?>
