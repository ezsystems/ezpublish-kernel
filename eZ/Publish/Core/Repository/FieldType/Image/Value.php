<?php
/**
 * File containing the Image Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Image;
use eZ\Publish\Core\Repository\FieldType\Value as BaseValue,
    eZ\Publish\Core\Repository\FieldType\ValueInterface,
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
     * @var \eZ\Publish\Core\Repository\FieldType\Image\AliasCollection
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
     * @return \eZ\Publish\Core\Repository\FieldType\Media\Value
     */
    public static function fromString( $stringValue )
    {
        return new static( $stringValue );
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value
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

            case 'mimeType':
                return $this->file->contentType->__toString();

            case 'mimeTypeCategory':
                return $this->file->contentType->type;

            case 'mimeTypePart':
                return $this->file->contentType->subType;

            case 'filesize':
                return $this->file->size;

            case 'filepath':
                return $this->file->path;

            default:
                throw new PropertyNotFound( $name, get_class() );
        }
    }
    /**
     * @see \eZ\Publish\Core\Repository\FieldType\ValueInterface::getTitle()
     */
    public function getTitle()
    {
        return !empty( $this->alternativeText ) ? $this->alternativeText : $this->originalFilename;
    }
}
