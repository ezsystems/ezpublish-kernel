<?php
namespace eZ\Publish\API\Repository\Values\IO;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This struct describes a file content type, as described in RFC 2045, RFC 2046,
 * RFC 2047, RFC 4288, RFC 4289 and RFC 2049.
 */
class ContentType  extends ValueObject
{
    /**
     * The type (audio, video, text, image)
     * @var string
     */
    public $type;

    /**
     * The subtype (mp3, mp4, plain, jpeg, ...)
     * @var string
     */
    public $subType;

    /**
     * Create object and set properties based on $mimeType
     *
     * @param $mimetype
     */
    public function __construct( $mimetype )
    {
        list( $this->type, $this->subType ) = explode( '/', $mimetype );
    }

    /**
     * Returns the ContentType's string representation: type/subtype
     *
     * @return string
     */
    public function __toString()
    {
        return "$this->type/$this->subType";
    }
}
