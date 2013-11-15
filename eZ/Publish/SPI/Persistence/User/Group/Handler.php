<?php

namespace eZ\Publish\SPI\Persistence\User\Group;

interface Handler
{
    /**
     * Creates a user group as child of $parentGroupId and returns it
     *
     * @param mixed $parentGroupId
     * @return \eZ\Publish\SPI\Persistence\User\Group
     */
    public function create( $parentGroupId );

    /**
     * Loads the group with the given $groupId
     *
     * @param mixed $groupId
     * @return \eZ\Publish\SPI\Persistence\User\Group
     */
    public function load( $groupId );

    /**
     * Loads all direct children of $parentGroupId
     *
     * @param mixed $parentGroupId
     * @return \eZ\Publish\SPI\Persistence\User\Group[]
     */
    public function loadSubGroups( $parentGroupId );

    /**
     * Moves $groupId below $newParentId
     *
     * @param mixed $groupId
     * @param mixed $newParentId
     */
    public function move( $groupId, $newParentId );
}
