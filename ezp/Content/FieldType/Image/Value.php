<?php
/**
 * File containing the Image Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Image;
use ezp\Content\FieldType\Value as BaseValue,
    ezp\Content\FieldType\ValueInterface,
    ezp\Base\BinaryRepository;

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
     * @param string $stringValue Image path (can be real path or relative to eZ Publish root).
     *
     * @return \ezp\Content\FieldType\Media\Value
     */
    public static function fromString( $stringValue )
    {
        $value = new static( $stringValue );
        return $value;
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public function __toString()
    {
        $string = $this->aliasList['original']->url;
        if ( isset( $this->alternativeText ) )
            $string .= "|$this->alternativeText";

        return $string;
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
     * @see \ezp\Content\FieldType\ValueInterface::getTitle()
     */
    public function getTitle()
    {
        if ( !empty( $this->alternativeText ) )
        {
            return $this->alternativeText;
        }
        else
        {
            return $this->originalFilename;
        }
    }
}
