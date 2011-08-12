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
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Locale,
    ezp\Content,
    ezp\Content\Query,
    ezp\Content\Query\Builder,
    ezp\Persistence\ValueObject,
    ezp\Persistence\Content as ContentValue;

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
     *
     * @param int $contentId
     * @return Content
     * @throws \ezp\Base\Exception\NotFound if content could not be found
     */
    public function load( $contentId )
    {
        $contentVO = $this->handler->contentHandler()->load( $contentId );
        if ( !$contentVO instanceof ContentValue )
            throw new NotFound( 'Content', $contentId );

        return $this->buildDomainObject( $contentVO );
    }

    /**
     * Finds content using a $query
     *
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
     * @throws \ezp\Base\Exception\NotFound if content could not be found
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

    protected function buildDomainObject( ValueObject $vo )
    {
        if ( !$vo instanceof ContentValue )
        {
            throw new InvalidArgumentType( 'Value object', 'ezp\\Persistence\\Content', $vo );
        }

        $content = new Content( new Type, new Locale( "eng-GB" ) );
        $content->setState(
            array(
                "section" => new Proxy( $this->repository->getSectionService(), $vo->sectionId ),
                "contentType" => new Proxy( $this->repository->getContentTypeService(), $vo->typeId ),
                "properties" => $vo
            )
        );

        /*
        // Container property (default sorting)
        $containerProperty = new ContainerProperty;
        $content->containerProperties[] = $containerProperty->setState(
            array(
                'contentId' => $vo->id,
                'sortField' => $vo->sortField,
                'sortOrder' => $vo->sortOrder,
                'location' => $content
            )
        );
        */

        return $content;
    }
}
?>
