<?php

/**
 * File containing the ValidationError class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType;

use eZ\Publish\SPI\FieldType\ValidationError as ValidationErrorInterface;
use eZ\Publish\API\Repository\Values\Translation\Message;
use eZ\Publish\API\Repository\Values\Translation\Plural;

/**
 * Class for validation errors.
 */
class ValidationError implements ValidationErrorInterface
{
    /** @var string */
    protected $singular;

    /** @var string */
    protected $plural;

    /** @var array */
    protected $values;

    /**
     * Element on which the error occurred
     * e.g. property name or property path compatible with Symfony PropertyAccess component.
     *
     * Example: StringLengthValidator[minStringLength]
     *
     * @var string
     */
    protected $target;

    /**
     * @param string $singular
     * @param string $plural
     * @param array $values
     */
    public function __construct($singular, $plural = null, array $values = [], $target = null)
    {
        $this->singular = $singular;
        $this->plural = $plural;
        $this->values = $values;
        $this->target = $target;
    }

    /**
     * Returns a translatable Message.
     *
     * @return \eZ\Publish\API\Repository\Values\Translation
     */
    public function getTranslatableMessage()
    {
        if (isset($this->plural)) {
            return new Plural(
                $this->singular,
                $this->plural,
                $this->values
            );
        } else {
            return new Message(
                $this->singular,
                $this->values
            );
        }
    }

    public function setTarget($target)
    {
        $this->target = $target;
    }

    public function getTarget()
    {
        return $this->target;
    }
}
