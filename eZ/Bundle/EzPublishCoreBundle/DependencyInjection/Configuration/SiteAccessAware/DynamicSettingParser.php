<?php
/**
 * File containing the DynamicSettingParser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;

use OutOfBoundsException;

class DynamicSettingParser implements DynamicSettingParserInterface
{
    public function isDynamicSetting( $setting )
    {
        // Checks if $setting begins and ends with appropriate delimiter.
        $result =
            is_string( $setting )
            && strpos( $setting, static::BOUNDARY_DELIMITER ) === 0
            && substr( $setting, -1 ) === static::BOUNDARY_DELIMITER;

        if ( $result === false )
        {
            return false;
        }

        return (bool)preg_match( '/^\$[a-zA-Z0-9_.-]+(?:(?:;[a-zA-Z0-9_.]+)(?:;[a-zA-Z0-9_.-]+)?)?\$$/', $setting );
    }

    public function parseDynamicSetting( $setting )
    {
        $params = explode( static::INNER_DELIMITER, $this->removeBoundaryDelimiter( $setting ) );
        if ( count( $params ) > 3 )
        {
            throw new OutOfBoundsException( "Dynamic settings cannot have more than 3 segments: \$paramName;namespace;scope\$" );
        }

        return array(
            'param' => $params[0],
            'namespace' => isset( $params[1] ) ? $params[1] : null,
            'scope' => isset( $params[2] ) ? $params[2] : null,
        );
    }

    /**
     * @param string $setting
     *
     * @return string
     */
    private function removeBoundaryDelimiter( $setting )
    {
        return substr( $setting, 1, -1 );
    }
}
