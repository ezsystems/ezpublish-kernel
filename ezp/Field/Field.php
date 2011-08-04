<?php
/**
 * File containing the Field class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Field;
use ezp\Field\FieldProperties;

/**
 * Base class for field types, the most basic storage unit of data inside eZ Publish.
 *
 * All other field types extend FieldType providing the specific functionality
 * desired in each case.
 *
 * The capabilities supported by each individual field type is decided by which
 * interfaces the field type implements support for. These individual
 * capabilities can also be checked via the supports*() methods.
 *
 * A field type are the base building blocks of Content Types, and serve as
 * data containers for Content objects. Therefore whiel field types can be used
 * independently, they are designed to be used as a part of a Content object.
 *
 * Field types are primed and preconfigured with the Field Definitions found in
 * Content Types.
 */
abstract class FieldType
{
    /**
     * @var string The textual identifier of the field type.
     */
    public $fieldTypeString;

    /**
     * @var mixed Fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     */
    protected $defaultValue;

    /**
     * @var boolean Flag telling whether or not the data is applicable for translation.
     */
    protected $isTranslatable;

    /**
     * @var boolean Flag telling whether search index extraction is applicable.
     */
    protected $isSearchable;

    /*
     * This flag is disabled for now. Information collection will probably be
     * carried with dedicated functionality, which will not require the need
     * fields to be configured specifically for this purpose.
     *
     * @var Flag deciding whether the field type can be used as an information collector.
     *
    protected $isInformationCollector;
    */

    /**
     * @var FieldProperties Custom properties which are specific to the field
     *                      type. Typically these properties are used to
     *                      configure behaviour of field types and normally set
     *                      in the FieldDefiniton on ContentTypes
     */
    protected $fieldProperties;
}