<?php

/**
 * Contains Invalid Argument Type Exception implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Exceptions;

use Exception;

/**
 * Invalid Argument Type Exception implementation.
 *
 * Usage: throw new InvalidArgument( 'nodes', 'array' );
 */
class InvalidArgumentValue extends InvalidArgumentException
{
    /**
     * Generates: "Argument '{$argumentName}' is invalid: '{$value}' is wrong value[ in class '{$className}']".
     *
     * @param string $argumentName
     * @param mixed $value
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param \Exception|null $previous
     */
    public function __construct($argumentName, $value, $className = null, Exception $previous = null)
    {
        $valueStr = is_string($value) ? $value : var_export($value, true);
        $parameters = ['%actualValue%' => $valueStr];
        $this->setMessageTemplate("'%actualValue%' is wrong value");
        if ($className) {
            $this->setMessageTemplate("'%actualValue%' is wrong value in class '%className%'");
            $parameters['%className%'] = $className;
        }
        $whatIsWrong = $this->getMessageTemplate();

        parent::__construct($argumentName, $whatIsWrong, $previous);

        // Alter the message template & inject new parameters.
        /** @Ignore */
        $this->setMessageTemplate(str_replace('%whatIsWrong%', $whatIsWrong, $this->getMessageTemplate()));
        $this->addParameters($parameters);
        $this->message = $this->getBaseTranslation();
    }
}
