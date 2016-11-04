<?php

/**
 * File containing the EMailAddress Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\EmailAddress;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for EMailAddress field type.
 */
class Value extends BaseValue
{
    /**
     * Email address.
     *
     * @var string
     */
    public $email;

    /**
     * Construct a new Value object and initialize its $email.
     *
     * @param string $email
     */
    public function __construct($email = '')
    {
        $this->email = $email;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->email;
    }
}
