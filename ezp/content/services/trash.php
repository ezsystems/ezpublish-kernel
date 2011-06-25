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
namespace ezp\content\Services;
use ezp\content\Content, ezp\base\ServiceInterface, ezp\base\Repository, ezp\base\StorageEngineInterface;

class Trash implements ServiceInterface
{
    /**
     * @var \ezx\base\Interfaces\Repository
     */
    protected $repository;

    /**
     * @var \ezp\base\StorageEngineInterface
     */
    protected $se;

    /**
     * Setups service with reference to repository object that created it & corresponding storage engine handler
     *
     * @param \ezp\base\Repository $repository
     * @param \ezp\base\StorageEngineInterface $se
     */
    public function __construct( Repository $repository,
                                 StorageEngineInterface $se )
    {
        $this->repository = $repository;
        $this->se = $se;
    }

    /**
     * Sends $content to trash
     *
     * @param \ezp\content\Content $content
     */
    public function trash( \ezp\content\Content $content )
    {

    }

    /**
     * Restores $content from trash
     *
     * @param \ezp\content\Content $content
     */
    public function untrash( \ezp\content\Content $content )
    {

    }
}
?>