<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\ViewUpdateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\MultiLanguageUpdateStructBase;

/**
 * This class is used for updating views
 */
class ViewUpdateStruct extends MultiLanguageUpdateStructBase
{

    /**
     * The query for the view
     *
     * @var Query;
     */
    public $query;

    /**
     * indicates if the view is usable for all users
     *
     * @var boolean
     */
    public $public;
}
