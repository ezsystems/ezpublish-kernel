<?php
namespace eZ\Publish\API\Repository\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation;

class ParentOwnerLimitation extends Limitation
{
    /**
     * (non-PHPdoc)
     * @see User/eZ\Publish\API\Repository\Values\User.Limitation::getIdentifier()
     */
    public function getIdentifier()
    {
        return Limitation::PARENTOWNER;
    }
}
