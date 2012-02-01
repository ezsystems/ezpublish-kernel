<?php
namespace eZ\Publish\API\Repository\Values\IO;

/**
 * This struct describes a file content type, as described in RFC 2045, RFC 2046,
 * RFC 2047, RFC 4288, RFC 4289 and RFC 2049.
 */
class ContentType
{
    public function __construct( $mimetype )
    {
        //@todo implement converstion from <type>/<subtype>
    }

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
     * Returns the ContentType's string representation: type/subtype
     */
    public function __toString()
    {
        return "$this->type/$this->subType";
    }
}
