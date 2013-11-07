<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\Type\FieldGroup class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence\Content\Type;


use eZ\Publish\SPI\Persistence\MultiLanguageValueBase;

/**
 * This class is used for categorizing field definitions inside a content type
 *
 * @package eZ\Publish\SPI\Persistence\Content\Type
 */
class FieldGroup extends MultiLanguageValueBase
{
    /**
     *
     * the id of the field group
     * @var mixed
     */
    public $id;
}
