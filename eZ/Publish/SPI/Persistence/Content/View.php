<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\View class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence\Content;


use eZ\Publish\SPI\Persistence\MultiLanguageValueBase;
use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * This class is returned by the view handler when loading views
 *
 * @package eZ\Publish\SPI\Persistence\Content
 */
class View extends MultiLanguageValueBase {
    /**
     * The unique system id
     *
     * @var mixed
     */
    public $id;

    /**
     * The query for the view
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query;
     */
    public $query;

    /**
     * The user which created the view
     *
     * @var mixed
     */
    public $userId;

    /**
     * indicates if the view is usable for all users
     *
     * @var boolean
     */
    public $public;
}
