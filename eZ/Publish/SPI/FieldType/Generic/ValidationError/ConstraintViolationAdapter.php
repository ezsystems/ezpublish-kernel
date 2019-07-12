<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\FieldType\Generic\ValidationError;

use eZ\Publish\API\Repository\Values\Translation;
use eZ\Publish\API\Repository\Values\Translation\Message;
use eZ\Publish\SPI\FieldType\ValidationError as ValidationErrorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * \Symfony\Component\Validator\ConstraintViolationInterface to eZ\Publish\SPI\FieldType\ValidationError adapter.
 */
final class ConstraintViolationAdapter implements ValidationErrorInterface
{
    /** @var \Symfony\Component\Validator\ConstraintViolationInterface */
    private $violation;

    /**
     * Element on which the error occurred
     * e.g. property name or property path compatible with Symfony PropertyAccess component.
     *
     * Example: StringLengthValidator[minStringLength]
     *
     * @var string
     */
    private $target;

    public function __construct(ConstraintViolationInterface $violation)
    {
        $this->violation = $violation;
        $this->target = $violation->getPropertyPath();
    }

    public function getTranslatableMessage(): Translation
    {
        return new Message(
            $this->violation->getMessageTemplate(),
            $this->violation->getParameters()
        );
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
