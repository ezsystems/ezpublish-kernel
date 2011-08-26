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
    ezp\Base\Collection\Lazy,
    ezp\Base\Exception\NotFound,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Content,
    ezp\Content\Location,
    ezp\Content\Version,
    ezp\Content\Query,
    ezp\Content\Query\Builder,
    ezp\Persistence\ValueObject,
    ezp\Persistence\Content as ContentValue,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\Location\CreateStruct as LocationCreateStruct,
    ezp\Persistence\Content\Criterion\ContentId,
    ezp\Persistence\Content\Criterion\Operator;

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
     * @throws \ezp\Base\Exception\InvalidArgumentType If $content already has an id
     * @todo If/when we have some sort of object storage, use that to check if object is persisted instead of just id
     */
    public function create( Content $content )
    {
        if ( $content->id )
            throw new InvalidArgumentType( '$content->id', 'false' );

        $struct = new CreateStruct();
        $this->fillStruct( $struct, $content, array( 'parentLocations', 'fields' ) );
        foreach ( $content->locations as $location )
        {
            // @todo: Generate pathIdentificationString?
            // @todo set sort order and fields based on settings in type
            $struct->parentLocations[] = $this->fillStruct(
                new LocationCreateStruct(
                    array(
                        "pathIdentificationString" => "",
                        "sortField" => Location::SORT_FIELD_PUBLISHED,
                        "sortOrder" => Location::SORT_ORDER_DESC,
                    )
                ),
                $location,
                array( "contentId", "contentVersion", "mainLocationId" )
            );
        }
        foreach ( $content->fields as $fields )
        {
            $struct->fields[] = $fields->getState( 'properties' );
        }
        $vo = $this->handler->contentHandler()->create( $struct  );
        return $this->buildDomainObject( $vo );
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
        $contentVO = $this->handler->contentHandler()->findSingle( new ContentId( $contentId ) );
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
        $this->handler->contentHandler()->delete( $content->id );
    }

    /**
     * List versions of a $content
     * @todo Clarify the reason why there is two ways to retrieve versions of a content:
     *       1. Using "Service->listVersions( $content )"
     *       2. Using "$content->versions"
     *       Is it because "2." accesses a Lazy collection configured with "1."
     *       Would it be possible to have 2. without 1.?
     *       Modify/remove this note at the same time than ezp\Content::getVersions()'s one
     *
     * @param Content $content
     * @return \ezp\Content\Version[]
     */
    public function listVersions( Content $content )
    {
        // @FIXME: should the return be an array or some sort of collection?
        $list = array();

        foreach ( $this->handler->contentHandler()->listVersions( $content->id ) as $versionVO )
        {
            $version = new Version( $content );
            $version->setState(
                array(
                    "properties" => $versionVO,
                    // @FIXME: should probably be a collection
                    "fields" => array(),
                )
            );
            $list[] = $version;
        }

        return $list;
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

    protected function buildDomainObject( ContentValue $vo )
    {
        $content = new Content( new Type );
        $content->setState(
            array(
                "section" => new Proxy( $this->repository->getSectionService(), $vo->sectionId ),
                "contentType" => new Proxy( $this->repository->getContentTypeService(), $vo->typeId ),
                "properties" => $vo
            )
        );

        $locationService = $this->repository->getLocationService();
        foreach ( $vo->locations as $locationValue )
        {
            $content->locations[] = $location = new Location( $content );
            $location->setState(
                array(
                    "properties" => $locationValue,
                    "parent" => new Proxy( $locationService, $locationValue->parentId ),
                    "children" => new Lazy(
                        "ezp\\Content\\Location",
                        $locationService,
                        $location, // api seems to use location to be able to get out sort info as well
                        "children" // Not implemented yet so this collection will return empty array atm
                    )
                )
            );
        }

        return $content;
    }
}
?>
