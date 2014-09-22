<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO\ParameterResolver;

use eZ\Publish\Core\IO\ParameterResolver;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Resolves the var directory based on a set of / glued settings
 */
class VarDirResolver implements ParameterResolver
{
    /**
     * Array of siteaccess aware settings that build-up the var dir
     * @var array
     */
    private $elements;

    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    public function __construct( array $elements, ConfigResolverInterface $configResolver )
    {
        $this->elements = $elements;
        $this->configResolver = $configResolver;
    }

    public function get()
    {
        $varDirParts = array();
        foreach ( $this->elements as $element )
        {
            $varDirParts[] = $this->configResolver->getParameter( $element );
        }
        return implode( '/', $varDirParts );
    }
}
