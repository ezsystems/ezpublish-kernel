<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\ViewCreateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\MultiLanguageCreateStructBase;
use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * This class is used for creating views
 */
class ViewCreateStruct extends MultiLanguageCreateStructBase
{
    /**
     * The query for the view
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query;
     */
    public $query;

    /**
     * indicates if the view is usable for all users
     *
     * @var boolean
     */
    public $public;
}
