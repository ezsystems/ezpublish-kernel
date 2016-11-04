<?php

/**
 * Contains Not Found Exception implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use Exception;
use eZ\Publish\Core\Base\Translatable;
use eZ\Publish\Core\Base\TranslatableBase;

/**
 * Not Found Exception implementation.
 *
 * Use:
 *   throw new NotFound( 'Content', 42 );
 */
class NotFoundException extends APINotFoundException implements Httpable, Translatable
{
    use TranslatableBase;

    /**
     * Generates: Could not find '{$what}' with identifier '{$identifier}'.
     *
     * @param string $what
     * @param mixed $identifier
     * @param \Exception|null $previous
     */
    public function __construct($what, $identifier, Exception $previous = null)
    {
        $identifierStr = is_string($identifier) ? $identifier : var_export($identifier, true);
        $this->setMessageTemplate("Could not find '%what%' with identifier '%identifier%'");
        $this->setParameters(['%what%' => $what, '%identifier%' => $identifierStr]);
        parent::__construct($this->getBaseTranslation(), self::NOT_FOUND, $previous);
    }
}
