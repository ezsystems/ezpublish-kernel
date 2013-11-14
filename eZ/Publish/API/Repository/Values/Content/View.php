<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\View class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\MultiLanguageValueBase;
use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * This class is returned by view service when loading views
 *
 * @property-read mixed  $id The unique system id
 * @property-read \eZ\Publish\API\Repository\Values\Content\Query  $query the query for the view
 * @property-read mixed  $userId the creator of the view
 * @proprety-read boolean $public indicates if the view is usable for all users
 */
abstract class View extends MultiLanguageValueBase
{

    /**
     * The unique system id
     *
     * @var mixed
     */
    protected $id;

    /**
     * The query for the view
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query;
     */
    protected $query;

    /**
     * The user which created the view
     *
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    protected $user;

    /**
     * indicates if the view is usable for all users
     *
     * @var boolean
     */
    protected $public;

}
