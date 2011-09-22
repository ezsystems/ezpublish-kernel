<?php
/**
 * File containing the TextLine Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\BinaryFile;
use ezp\Content\FieldType\Value as ValueInterface,
    ezp\Persistence\Content\FieldValue as PersistenceFieldValue,
    ezp\Io\BinaryFile;

/**
 * Value for TextLine field type
 */
class Value implements ValueInterface
{
    /**
     * BinaryFile object
     *
     * @var \ezp\Io\BinaryFile
     */
    public $file;

    /**
     * Original file name
     *
     * @var string
     */
    public $originalFilename;

    /**
     * @var \ezp\Content\FieldType\BinaryFile\Handler
     */
    protected $handler;

    /**
     * Construct a new Value object and initialize its $binaryFile
     */
    public function __construct()
    {
        $this->handler = new Handler;
    }

    /**
     * @see \ezp\Content\FieldType\Value
     * @return \ezp\Content\FieldType\BinaryFile\Value
     */
    public static function fromString( $stringValue )
    {
        $value = new static();
        $value->file = $value->handler->createFromLocalPath( $stringValue );
        $value->originalFilename = basename( $stringValue );
        return $value;
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public function __toString()
    {
        return $this->file->path;
    }
}
