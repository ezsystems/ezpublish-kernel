<?php
/**
 * File containing the LocaleConverter class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Locale;

use Psr\Log\LoggerInterface;

class LocaleConverter implements LocaleConverterInterface
{
    /**
     * Conversion map, indexed by eZ Publish locale.
     * See locale.yml
     *
     * @var array
     */
    private $conversionMap;

    /**
     * Conversion map, indexed by POSIX locale.
     *
     * @var array
     */
    private $reverseConversionMap;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct( array $conversionMap, LoggerInterface $logger )
    {
        $this->conversionMap = $conversionMap;
        $this->reverseConversionMap = array_flip( $conversionMap );
        $this->logger = $logger;
    }

    /**
     * Converts a locale in eZ Publish internal format to POSIX format.
     * Returns null if conversion cannot be made.
     *
     * @param string $ezpLocale
     * @return string|null
     */
    public function convertToPOSIX( $ezpLocale )
    {
        if ( !isset( $this->conversionMap[$ezpLocale] ) )
        {
            $this->logger->warning( "Could not convert locale '$ezpLocale' to POSIX format. Please check your locale configuration in ezpublish.yml" );
            return;
        }

        return $this->conversionMap[$ezpLocale];
    }

    /**
     * Converts a locale in POSIX format to eZ Publish internal format.
     * Returns null if conversion cannot be made.
     *
     * @param string $posixLocale
     * @return string|null
     */
    public function convertToEz( $posixLocale )
    {
        if ( !isset( $this->reverseConversionMap[$posixLocale] ) )
        {
            $this->logger->warning( "Could not convert locale '$posixLocale' to eZ Publish format. Please check your locale configuration in ezpublish.yml" );
            return;
        }

        return $this->reverseConversionMap[$posixLocale];
    }
}
