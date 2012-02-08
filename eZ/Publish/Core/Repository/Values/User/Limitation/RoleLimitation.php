<?php
namespace eZ\Publish\Core\Repository\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation as APIRoleLimitation;

class RoleLimitation extends APIRoleLimitation
{
    /**
     *
     * the custom limitation name
     * @var string
     */
    private $name;

    /**
     * constructs a role limitation for the given limitation name
     * @param string $name
     */
    public function __construct( $name )
    {
        $this->name = $name;
    }

    /**
     * Returns the limitation identifer
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->name;
    }
}
