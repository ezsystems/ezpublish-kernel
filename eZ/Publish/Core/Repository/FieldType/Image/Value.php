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
    eZ\Publish\API\Repository\IOService;

/**
 * Value for Image field type
 *
 * @todo Rewrite image fieldtype
 *
 * @property string $filename The name of the file in the eZ publish var directory
 *                            (for example "44b963c9e8d1ffa80cbb08e84d576735.avi").
 * @property string $mimeType
 */
class Value extends BaseValue
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
     *
     * @param \eZ\Publish\API\Repository\IOService $IOService
     * @param string|null $file
     * @param string $alternativeText
     */
    public function __construct( IOService $IOService, $file = null, $alternativeText = '' )
    {
        $this->alternativeText = $alternativeText;
        $this->aliasList = new AliasCollection( $this, $IOService );
        $this->aliasList->initializeFromLocalImage( $file );
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public function __toString()
    {
        $string = $this->aliasList['original']->url;
        if ( !empty( $this->alternativeText ) )
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
                return basename( $this->file->id );

            case 'mimeType':
                return $this->file->contentType;

            case 'filesize':
                return $this->file->size;

            case 'filepath':
                return $this->file->path;

            default:
                throw new PropertyNotFound( $name, get_class() );
        }
    }
    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value::getTitle()
     */
    public function getTitle()
    {
        return !empty( $this->alternativeText ) ? $this->alternativeText : $this->originalFilename;
    }
}
