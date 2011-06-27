<?php
/**
 * File containing the ezp\content\Services\Content class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

/**
 * Subtree service, used for complex subtree operations
 * @package ezp
 * @subpackage content
 */
namespace ezp\content\Services;
use ezp\content\Content, ezp\base\ServiceInterface, ezp\base\Repository, ezp\base\StorageEngineInterface;

class Content implements ServiceInterface
{
    /**
     * @var \ezp\base\Repository
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
     * Creates the new $content in the content repository
     *
     * @param \ezp\content\Content $content
     *
     * @return \ezp\content\Content The newly created content
     * @throws \ezp\content\ValidationException If a validation problem has been found for $content
     */
    public function create( Content $content )
    {
        // @todo : Do any necessary actions to insert $content in the content repository
        // go through all locations to create or update them
        return $content;
    }

    /**
     * Updates $content in the content repository
     * @param \ezp\content\Content $content
     * @return $content
     * @throws \ezp\content\ValidationException If a validation problem has been found for $content
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
     * @return \ezp\content\Content
     * @throws \ezp\content\ContentNotFoundException if content could not be found
     */
    public function load( $contentId )
    {
        $content = $this->se->getContentHandler()->load( $contentId );
        if ( !$content )
            throw new \ezp\content\ContentNotFoundException( $contentId );
        return $content;
    }


    /**
     * Deletes a content from the repository
     *
     * @param \ezp\content\Content $content
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
     * Creates a new criteria collection object in order to query the content repository
     * @return \ezp\content\Criteria\CriteriaCollection
     */
    public function createCriteria()
    {
        return new \ezp\content\Criteria\CriteriaCollection();
    }
}
?>
