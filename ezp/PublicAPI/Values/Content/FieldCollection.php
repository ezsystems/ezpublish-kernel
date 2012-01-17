<?php
namespace ezp\PublicAPI\Values\Content;

use ezp\PublicAPI\Values\Content\TranslatableField;
use ezp\PublicAPI\Values\Content\Field;

/**
 *
 * This class is used for magic getters and setters in Version, VersionUpdate, ContentCreate and Translation
 *
 *
 */
abstract class FieldCollection implements ArrayAccess {
    /**
     *
     * key value map with key field definition identifier
     * @var map of {@link Field} or {@link TranslatableField}
     */
    protected $fields;
}