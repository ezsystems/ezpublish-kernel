<?php
/**
 * File containing the StorageFieldDefinition class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

class StorageFieldDefinition extends ValueObject
{
    /**
     * Data float 1
     *
     * @var float
     */
    public $dataFloat1;

    /**
     * Data float 2
     *
     * @var float
     */
    public $dataFloat2;

    /**
     * Data float 3
     *
     * @var float
     */
    public $dataFloat3;

    /**
     * Data float 4
     *
     * @var float
     */
    public $dataFloat4;

    /**
     * Data int 1
     *
     * @var int
     */
    public $dataInt1;

    /**
     * Data int 2
     *
     * @var int
     */
    public $dataInt2;

    /**
     * Data int 3
     *
     * @var int
     */
    public $dataInt3;

    /**
     * Data int 4
     *
     * @var int
     */
    public $dataInt4;

    /**
     * Data text 1
     *
     * @var string
     */
    public $dataText1;

    /**
     * Data text 2
     *
     * @var string
     */
    public $dataText2;

    /**
     * Data text 3
     *
     * @var string
     */
    public $dataText3;

    /**
     * Data text 4
     *
     * @var string
     */
    public $dataText4;

    /**
     * Data text 5
     *
     * @var string
     */
    public $dataText5;

    /**
     * Data text I18n
     *
     * @var string[]
     */
    public $serializedDataText;
}
