<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\EventListener;

/**
 * Matches an URI against a list of prefixes
 */
class PrefixesIoUriMatcher implements IoUriMatcher
{
    /** @var string */
    private $urlPrefix;

    /** @var array */
    private $binaryPrefixes;

    public function __construct( array $prefixes )
    {
        $this->prefixes = array_map(
            function( $value )
            {
                return ltrim( '/', $value );
            },
            $prefixes
        );
    }

    public function matches( $uri )
    {
        foreach ( $this->prefixes as $prefix )
        {
            if ( strpos( $uri, $prefix ) === 0 )
            {
                return true;
            }
        }
        return false;
    }
}
