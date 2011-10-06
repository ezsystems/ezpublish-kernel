<?php
/**
 * File containing the Image Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Image;
use ezp\Content\FieldType\Value as BaseValue,
    ezp\Content\FieldType\ValueInterface;

/**
 * Value for Image field type
 */
class Value extends BaseValue implements ValueInterface
{
    /**
     * The alternative image text (for example "Picture of an apple.").
     *
     * @var string
     */
    public $alternativeText;

    /**
     * Original file name
     *
     * @var string
     */
    public $originalFilename;

    /**
     *
     * @var bool
     */
    public $isValid;

    /**
     * Image alias list, indexed by alias name
     *
     * @var \ezp\Content\FieldType\Image\AliasCollection
     */
    public $aliasList;

    /**
     * @var \ezp\Content\FieldType\Image\Handler
     */
    protected $handler;

    protected $properties = array(
        'fieldId' => null,
        'contentId' => null,
        'versionNo' => null,
        // Publication status (one of \ezp\Content\Version::STATUS_*)
        'status' => null,
        'languageCode' => null,
    );

    /**
     * Construct a new Value object.
     */
    public function __construct( $imagePath = null, $alternativeText = '' )
    {
        $this->alternativeText = $alternativeText;
        $this->aliasList = new AliasCollection( $this, new BinaryRepository );
        $this->aliasList->initializeFromLocalImage( $imagePath );
    }

    /**
     * @see \ezp\Content\FieldType\Value
     * @return \ezp\Content\FieldType\Media\Value
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

    /**
     * Magic getter
     *
     * @param string $name
     */
    public function __get( $name )
    {
        switch ( $name )
        {
            case 'filename':
                return basename( $this->file->path );
                break;

            case 'mimeType':
                return $this->file->contentType->__toString();
                break;

            case 'mimeTypeCategory':
                return $this->file->contentType->type;
                break;

            case 'mimeTypePart':
                return $this->file->contentType->subType;
                break;

            case 'filesize':
                return $this->file->size;
                break;

            case 'filepath':
                return $this->file->path;
                break;

            default:
                throw new PropertyNotFound( $name, get_class() );
        }
    }

    /**
     * Returns handler object.
     * Useful manipulate {@link self::$file}
     *
     * @return \ezp\Content\FieldType\Media\Handler
     */
    public function getHandler()
    {
        return $this->handler;
    }
}
