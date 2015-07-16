<?php
/**
 * File containing the eZ\Publish\API\Repository\VersionStateService interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\VersionState\VersionStateUpdateStruct;
use eZ\Publish\API\Repository\Values\VersionState\VersionStateCreateStruct;
use eZ\Publish\API\Repository\Values\VersionState\VersionState;

/**
 * VersionStateService service
 *
 * @package eZ\Publish\API\Repository
 */
interface VersionStateService
{
    /**
     * This method returns the ordered list of version states
     *
     * @return \eZ\Publish\API\Repository\Values\VersionState\VersionState[]
     */
    public function loadVersionStates();

    /**
     * Creates a new version state in the given group.
     *
     * Note: in current kernel: If it is the first state all content versions will
     * set to this state.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create an version state
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the version state with provided identifier already exists
     *
     * @param \eZ\Publish\API\Repository\Values\VersionState\VersionStateCreateStruct $VersionStateCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\VersionState\VersionState
     */
    public function createVersionState( VersionStateCreateStruct $VersionStateCreateStruct );

    /**
     * Loads an version state
     *
     * @param mixed $stateId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the state was not found
     *
     * @return \eZ\Publish\API\Repository\Values\VersionState\VersionState
     */
    public function loadVersionState( $stateId );

    /**
     * Updates an version state
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update an version state
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the version state with provided identifier already exists
     *
     * @param \eZ\Publish\API\Repository\Values\VersionState\VersionState $VersionState
     * @param \eZ\Publish\API\Repository\Values\VersionState\VersionStateUpdateStruct $VersionStateUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\VersionState\VersionState
     */
    public function updateVersionState( VersionState $VersionState, VersionStateUpdateStruct $VersionStateUpdateStruct );

    /**
     * Deletes a version state. The state of the content versions is reset to the
     * first version state in the group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete an version state
     *
     * @param \eZ\Publish\API\Repository\Values\VersionState\VersionState $VersionState
     */
    public function deleteVersionState( VersionState $VersionState );

    /**
     * Sets the version-state of a state group to $state for the given content.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to change the version state
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\API\Repository\Values\VersionState\VersionState $VersionState
     */
    public function setVersionState( VersionInfo $versionInfo, VersionState $VersionState );

    /**
     * Gets the version-state of version identified by $contentId.
     *
     * The $state is the id of the state within one group.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return \eZ\Publish\API\Repository\Values\VersionState\VersionState
     */
    public function getVersionState( VersionInfo $versionInfo );

    /**
     * Returns the number of versions which are in this state
     *
     * @param \eZ\Publish\API\Repository\Values\VersionState\VersionState $VersionState
     *
     * @return int
     */
    public function getVersionCount( VersionState $VersionState );

    /**
     * Instantiates a new version State Create Struct and sets $identifier in it.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\VersionState\VersionStateCreateStruct
     */
    public function newVersionStateCreateStruct( $identifier );

    /**
     * Instantiates a new version State Update Struct.
     *
     * @return \eZ\Publish\API\Repository\Values\VersionState\VersionStateUpdateStruct
     */
    public function newVersionStateUpdateStruct();
}
