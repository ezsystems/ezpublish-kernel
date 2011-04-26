<?php
/**
 * File containing ContentInterface class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license //EZP_LICENCE//
 * @version //autogentag//
 * @package ezpublish
 * @subpackage persistence
 */
namespace ezp\Content\Persistence\API;

/**
 * Content interface
 */
interface ContentInterface extends BaseContentInterface
{
    /**
     * Must return an implementation of ezp\User\Persistence\API\UserInterface
     * This implementation MUST inherit from ezp\User\Persistence\API\BaseUser
     * @return ezp\User\Persistence\API\BaseUser
     */
    public function getOwner();

    /**
     * Must return an implementation of ContentSectionInterface
     * This implementation MUST inherit from BaseContentSection
     * @return BaseContentSection
     */
    public function getSection();

    /**
     * Must return an implementation of ContentTypeInterface
     * This implementation MUST inherit from BaseContentType
     * @return BaseContentType
     */
    public function getType();

    /**
     * A content relation inherits from a content,
     * providing additionnal information like the relation type.
     * Thus, it also implement the ContentInterface
     * @return array( BaseContentRelation )
     */
    public function getRelations();

    /**
     * @return array( BaseContentVersion )
     */
    public function getVersions();

    /**
     * @return BaseContentVersion
     */
    public function getCurrentVersion();

    /**
     * @return array( BaseLocation )
     */
    public function getLocations();

    /**
     * @return BaseLocation
     */
    public function getMainLocation();
}
?>