<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\View\Handler class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence\Content\View;

use eZ\Publish\SPI\Persistence\Content\View\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\View\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\View;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * The view handler provides CRUD operations for views.
 */
interface Handler
{
    /**
     * Create a view
     *
     * @param \eZ\Publish\SPI\Persistence\Content\View\CreateStruct $viewCreateStruct
     *
     */
    public function create( CreateStruct $viewCreateStruct );

    /**
     * loads a view for the given $viewId
     *
     * @param mixed $viewId the id of the view
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the view is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\View
     */
    public function load( $viewId );

    /**
     * loads a view for the given identifier
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the view is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\View
     */
    public function loadByIdentifier( $identifier );

    /**
     * loads all views for the given user
     *
     * @param mixed $userId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\View[]
     */
    public function loadViews( $userId );

    /**
     * loads all views. If $publicOnly is true then only public views a returned
     *
     * @param boolean $publicOnly
     *
     * @return \eZ\Publish\SPI\Persistence\Content\View[]
     */
    public function loadAllViews( $publicOnly );

    /**
     * Updates the view with the given $viewId
     *
     * @param mixed $viewId
     * @param \eZ\Publish\SPI\Persistence\Content\View\UpdateStruct $viewUpdateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\View the updated view
     */
    public function update( $viewId, UpdateStruct $viewUpdateStruct );

    /**
     * deletes the view with the given $viewId
     *
     * @param mixed $viewId
     *
     */
    public function delete( $viewId );
}
