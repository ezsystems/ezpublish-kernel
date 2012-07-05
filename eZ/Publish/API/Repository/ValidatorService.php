<?php
/**
 * File containing the eZ\Publish\API\Repository\ValidatorService object interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\API\Repository;

/**
 * @internal
 * @package eZ\Publish\API\Repository
 */
interface ValidatorService
{
    /**
     * The namespace under which concrete validator classes reside
     */
    const CONCRETE_VALIDATOR_NAMESPACE = "eZ\\Publish\\Core\\Repository\\FieldType\\Validator";

    /**
     * The namespace under which public domain validator classes reside
     */
    const PUBLIC_VALIDATOR_NAMESPACE = "eZ\\Publish\\Core\\Repository\\Values\\ContentType\\Validator";

    /**
     * Returns concrete validator object from given validator identifier
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Validator
     */
    public function getValidator( $identifier );

    /**
     * Returns public domain validator representation object from given validator identifier
     *
     * @param string $identifier
     * @param array $constraints
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\Validator
     */
    public function buildValidatorDomainObject( $identifier, array $constraints );
}
