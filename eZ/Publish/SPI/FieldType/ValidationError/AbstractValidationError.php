<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\FieldType\ValidationError;

use eZ\Publish\API\Repository\Values\Translation;
use eZ\Publish\API\Repository\Values\Translation\Message;
use eZ\Publish\SPI\FieldType\ValidationError;

/**
 * @internal
 */
abstract class AbstractValidationError implements ValidationError
{
    /** @var string */
    protected $message;

    /** @var array */
    protected $parameters;

    /**
     * Element on which the error occurred
     * e.g. property name or property path compatible with Symfony PropertyAccess component.
     *
     * Example: StringLengthValidator[minStringLength]
     *
     * @var string
     */
    protected $target;

    public function __construct(string $message, array $parameters, string $target)
    {
        $this->message = $message;
        $this->parameters = $parameters;
        $this->target = $target;
    }

    public function getTranslatableMessage(): Translation
    {
        return new Message($this->message, $this->parameters);
    }

    public function setTarget($target): void
    {
        $this->target = $target;
    }

    public function getTarget(): string
    {
        return $this->target;
    }
}
