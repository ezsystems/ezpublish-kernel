<?php

/**
 * Contains BadState Exception implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\BadStateException as APIBadStateException;
use Exception;
use eZ\Publish\Core\Base\Translatable;
use eZ\Publish\Core\Base\TranslatableBase;

/**
 * BadState Exception implementation.
 *
 * Usage: throw new BadState( 'nodes', 'array' );
 */
class BadStateException extends APIBadStateException implements Translatable
{
    use TranslatableBase;

    /**
     * Generates: "Argument '{$argumentName}' has a bad state: {$whatIsWrong}".
     *
     * @param string $argumentName
     * @param string $whatIsWrong
     * @param \Exception|null $previous
     */
    public function __construct($argumentName, $whatIsWrong, Exception $previous = null)
    {
        $this->setMessageTemplate("Argument '%argumentName%' has a bad state: %whatIsWrong%");
        $this->setParameters(['%argumentName%' => $argumentName, '%whatIsWrong%' => $whatIsWrong]);
        parent::__construct($this->getBaseTranslation(), 0, $previous);
    }
}
