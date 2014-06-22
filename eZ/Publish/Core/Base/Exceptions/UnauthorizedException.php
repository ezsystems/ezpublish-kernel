<?php
/**
 * Contains UnauthorizedException Exception implementation
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException as APIUnauthorizedException;
use Exception;

/**
 * UnauthorizedException Exception implementation
 *
 * Use:
 *   throw new UnauthorizedException( 'content', 'read', array( 'contentId' => 42 ) );
 */
class UnauthorizedException extends APIUnauthorizedException implements Httpable
{
    /**
     * Generates: User does not have access to '{$function}' '{$module}'[ with: %property.key% '%property.value%']
     *
     * Example: User does not have access to 'read' 'content' with: id '44', type 'article'
     *
     * @param string $module The module name should be in sync with the name of the domain object in question
     * @param string $function
     * @param array $properties Key value pair with non sensitive data on what kind of data user does not have access to
     * @param \Exception|null $previous
     */
    public function __construct( $module, $function, array $properties = null, Exception $previous = null )
    {
        $identificationString = '';
        if ( $properties !== null )
        {
            foreach ( $properties as $name => $value )
            {
                $identificationString .= $identificationString === '' ? ' with:' : ',';
                $identificationString .= " {$name} '{$value}'";
            }
        }

        parent::__construct(
            "User does not have access to '{$function}' '{$module}'" . $identificationString,
            self::UNAUTHORIZED,
            $previous
        );
    }
}
