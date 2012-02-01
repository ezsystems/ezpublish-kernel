<?php
namespace ezp\PublicAPI\Values\User\Limitation;

use ezp\PublicAPI\Values\User\Limitation;

class CustomLimitation extends Limitation
{
    /**
     *
     * the custom limitation name
     * @var string
     */
    private $name;

    /**
     * constructs a custom limitation for the given limitation name
     * @param string $name
     */
    public function __construct( $name )
    {
        $this->name = $name;
    }

    /**
     * (non-PHPdoc)
     * @see User/ezp\PublicAPI\Values\User.Limitation::getIdentifier()
     */
    public function getIdentifier()
    {
        return $name;
    }
}
