<?php
/**
 * File containing the ezp\Content\Services\Trash class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package Content
 * @subpackages Services
 */

/**
 * Trash service, used for content trash handling
 * @package Content
 * @subpackage Services
 */
namespace ezp\Content\Services;

class Trash implements ezp\ServiceInterface
{
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
    public function untrash( \ezp\Content\Content $content )
    {

    }
}
?>