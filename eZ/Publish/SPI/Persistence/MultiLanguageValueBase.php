<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\MultiLanguageValueBase class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence;


class MultiLanguageValueBase
{
    /**
     * Human readable multi language names of the object
     *
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<name_eng>', 'de' => '<name_de>' );
     * </code>
     *
     * @var string[]
     */
    public $names;

    /**
     * Human readable multi language descriptions of the object
     *
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @var string[]
     */
    public $descriptions = array();

    /**
     * String identifier of the object
     *
     * @var string
     */
    public $identifier;

    /**
     * the main language
     *
     * @var string
     */
    public $mainLanguageCode;
}
