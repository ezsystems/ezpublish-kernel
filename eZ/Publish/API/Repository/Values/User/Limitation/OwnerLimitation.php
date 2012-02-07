<?php
namespace eZ\Publish\API\Repository\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation;

class OwnerLimitation extends Limitation
{
    /**
     * @see \eZ\Publish\API\Repository\Values\User\Limitation::getIdentifier()
     *
     * @return string
     */
    public function getIdentifier()
    {
        return Limitation::OWNER;
    }
}
