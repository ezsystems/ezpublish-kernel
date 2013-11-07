<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\View\UpdateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence\Content\View;


use eZ\Publish\SPI\Persistence\MultiLanguageValueBase;
use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * This class is used to update views
 */
class UpdateStruct extends MultiLanguageValueBase
{
    /**
     * If set the query for the view is updated
     *
     * @var Query;
     */
    public $query;

    /**
     * if set the public indicator is updated
     *
     * @var boolean
     */
    public $public;
}
