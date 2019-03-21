<?php

/**
 * Contains UnauthorizedException Exception implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException as APIUnauthorizedException;
use Exception;
use eZ\Publish\Core\Base\Translatable;
use eZ\Publish\Core\Base\TranslatableBase;

/**
 * UnauthorizedException Exception implementation.
 *
 * Use:
 *   throw new UnauthorizedException( 'content', 'read', array( 'contentId' => 42 ) );
 */
class UnauthorizedException extends APIUnauthorizedException implements Httpable, Translatable
{
    use TranslatableBase;

    /**
     * Generates: User does not have access to '{$function}' '{$module}'[ with: %property.key% '%property.value%'].
     *
     * Example: User does not have access to 'read' 'content' with: id '44', type 'article'
     *
     * @param string $module The module name should be in sync with the name of the domain object in question
     * @param string $function
     * @param array $properties Key value pair with non sensitive data on what kind of data user does not have access to
     * @param \Exception|null $previous
     */
    public function __construct($module, $function, array $properties = null, Exception $previous = null)
    {
        $this->setMessageTemplate("User does not have access to '%function%' '%module%'");
        $this->setParameters(['%module%' => $module, '%function%' => $function]);

        if ($properties) {
            $this->setMessageTemplate("User does not have access to '%function%' '%module%' with: %with%");
            $with = [];
            foreach ($properties as $name => $value) {
                $with[] = "{$name} '{$value}'";
            }
            $this->addParameter('%with%', implode(', ', $with));
        }

        parent::__construct($this->getBaseTranslation(), self::UNAUTHORIZED, $previous);
    }
}
