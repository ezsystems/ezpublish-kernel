<?php
/**
 * File containing the ezp\content\Services\Trash class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

namespace ezp\content\Services;

/**
 * Trash service, used for content trash handling
 *
 * Notes:
 * Moving to trash is currently the same as moving to a custom subtree, not directly visible from the outside.
 * When a Content is moved to the trash, it should remember its previous locations, so that it can be moved there
 * again if restored. We therefore most likely need extra informations in order to be able to do that.
 * Is it possible to achieve this in the business layer only, or do we need extra storage ?
 *
 * @package ezp
 * @subpackage content
 */
use ezp\content\Content;
class Trash extends \ezp\base\AbstractService
{
    /**
     * Sends $content to trash
     *
     * @param \ezp\content\Content $content
     */
    public function trash( Content $content )
    {

    }

    /**
     * Restores $content from trash
     *
     * @param \ezp\content\Content $content
     */
    public function unTrash( Content $content )
    {

    }
}
?>