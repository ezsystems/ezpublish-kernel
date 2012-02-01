<?php
namespace eZ\Publish\API\Values\User\Limitation;

use eZ\Publish\API\Values\User\Limitation;

class SectionLimitation extends RoleLimitation
{
    /**
     * (non-PHPdoc)
     * @see User/eZ\Publish\API\Values\User.Limitation::getIdentifier()
     */
    public function getIdentifier()
    {
        return Limitation::SECTION;
    }
}
