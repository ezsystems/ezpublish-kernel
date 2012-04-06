<?php
/**
 * File containing the Persistence Handler interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence;

/**
 * The main handler for Storage Engine
 */
interface Handler
{
    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function contentHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    public function searchHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    public function contentTypeHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    public function contentLanguageHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function locationHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function trashHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\User\Handler
     */
    public function userHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function sectionHandler();

    /**
     */
    public function beginTransaction();

    /**
     */
    public function commit();

    /**
     */
    public function rollback();
}
