<?php
/**
 * File containing the eZ\Publish\API\Repository\ViewService class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\API\Repository;
use eZ\Publish\API\Repository\Values\Content\ViewCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ViewUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\View;
use eZ\Publish\API\Repository\Values\User\User;

/**
 * The ViewService provides methods for loading, creating, deleting and updating views
 *
 */
interface ViewService
{

    /**
     * Create a view
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to create views
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the view is public and another public
     * view with the same identifier exists
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ViewCreateStruct $viewCreateStruct
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return \eZ\Publish\API\Repository\Values\Content\View
     */
    public function createView( ViewCreateStruct $viewCreateStruct, User $user = null );

    /**
     * loads a view for the given $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the view is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to load the view
     *
     * @param mixed $id the id of the view
     *
     * @return \eZ\Publish\API\Repository\Values\Content\View
     */
    public function loadView( $id );

    /**
     * loads a view for the given identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the view is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to load the view
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\Content\View
     */
    public function loadByIdentifier( $identifier );

    /**
     * loads all views for the given user. If $user is null then the view of the current user are returned
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return \eZ\Publish\API\Repository\Values\Content\View[]
     */
    public function loadViews( User $user = null );

    /**
     * loads all views. If $publicOnly is true then only public views a returned
     *
     * @param boolean $publicOnly
     *
     * @return \eZ\Publish\API\Repository\Values\Content\View[]
     */
    public function loadAllViews( $publicOnly );

    /**
     * Updates the view with the given $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to update the view
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the view is public and another public
     * view with the same identifier exists
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ViewUpdateStruct $viewUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\Content\View  $view
     *
     * @return \eZ\Publish\API\Repository\Values\Content\View the updated view
     */
    public function updateView( View $view, ViewUpdateStruct $viewUpdateStruct );

    /**
     * deletes the given view
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to delete the view
     *
     * @param \eZ\Publish\API\Repository\Values\Content\View  $view
     *
     */
    public function deleteView( View $view );
}
