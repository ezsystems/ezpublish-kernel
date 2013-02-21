<?php
/**
 * File containing the Utf8Converter class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search;

use RuntimeException;

/**
 * Class for converting UTF-8 characters to their decimal code points and vice
 * versa.
 *
 * The code originates from the eZUTF8Codec class available in lib/ezi18n/classes/ezutf8codec.php
 */
class Utf8Converter
{
    /**
     * Convert character code to UTF-8 character
     *
     * @param int $charCode
     *
     * @return string
     */
    public static function toUTF8Character( $charCode )
    {
        switch ( $charCode )
        {
            case 0:
                $char = chr( 0 );
                break;
            case !( $charCode & 0xffffff80 ): // 7 bit
                $char = chr( $charCode );
                break;
            case !( $charCode & 0xfffff800 ): // 11 bit
                $char = (
                    chr( 0xc0 | ( ( $charCode >> 6 ) & 0x1f ) ) .
                    chr( 0x80 | ( $charCode & 0x3f ) )
                );
                break;
            case !( $charCode & 0xffff0000 ): // 16 bit
                $char = (
                    chr( 0xe0 | ( ( $charCode >> 12 ) & 0x0f ) ) .
                    chr( 0x80 | ( ( $charCode >> 6 ) & 0x3f ) ) .
                    chr( 0x80 | ( $charCode & 0x3f ) )
                );
                break;
            case !( $charCode & 0xffe00000 ): // 21 bit
                $char = (
                    chr( 0xf0 | ( ( $charCode >> 18 ) & 0x07 ) ) .
                    chr( 0x80 | ( ( $charCode >> 12 ) & 0x3f ) ) .
                    chr( 0x80 | ( ( $charCode >> 6 ) & 0x3f ) ) .
                    chr( 0x80 | ( $charCode & 0x3f ) )
                );
                break;
            case !( $charCode & 0xfc000000 ): // 26 bit
                $char = (
                    chr( 0xf8 | ( ( $charCode >> 24 ) & 0x03 ) ) .
                    chr( 0x80 | ( ( $charCode >> 18 ) & 0x3f ) ) .
                    chr( 0x80 | ( ( $charCode >> 12 ) & 0x3f ) ) .
                    chr( 0x80 | ( ( $charCode >> 6 ) & 0x3f ) ) .
                    chr( 0x80 | ( $charCode & 0x3f ) )
                );
                break;
            default: // 31 bit
                $char = (
                    chr( 0xfc | ( ( $charCode >> 30 ) & 0x01 ) ) .
                    chr( 0x80 | ( ( $charCode >> 24 ) & 0x3f ) ) .
                    chr( 0x80 | ( ( $charCode >> 18 ) & 0x3f ) ) .
                    chr( 0x80 | ( ( $charCode >> 12 ) & 0x3f ) ) .
                    chr( 0x80 | ( ( $charCode >> 6 ) & 0x3f ) ) .
                    chr( 0x80 | ( $charCode & 0x3f ) )
                );
        }

        return $char;
    }

    /**
     * Convert a single UTF-8 character into its decimal code point
     *
     * @param string $char
     *
     * @return int
     */
    public static function toUnicodeCodepoint( $char )
    {
        $charCode = false;
        // 7bits, 1 char
        if ( ( ord( $char[0] ) & 0x80 ) == 0x00 )
        {
            $charCode = ord( $char[0] );
        }
        // 11 bits, 2 chars
        else if ( ( ord( $char[0] ) & 0xe0 ) == 0xc0 )
        {
            if ( ( ord( $char[1] ) & 0xc0 ) != 0x80 )
            {
                return $charCode;
            }

            $charCode = (
                ( ( ord( $char[0] ) & 0x1f ) << 6 ) +
                ( ( ord( $char[1] ) & 0x3f ) )
            );

            if ( $charCode < 128 )
            {
                throw new RuntimeException( 'Illegal UTF-8 input character: ' . $char );
            }
        }
        // 16 bits, 3 chars
        else if ( ( ord( $char[0] ) & 0xf0 ) == 0xe0 )
        {
            if ( ( ord( $char[1] ) & 0xc0 ) != 0x80 ||
                 ( ord( $char[2] ) & 0xc0 ) != 0x80 )
            {
                return $charCode;
            }
            $charCode = (
                ( ( ord( $char[0] ) & 0x0f ) << 12 ) +
                ( ( ord( $char[1] ) & 0x3f ) << 6 ) +
                ( ( ord( $char[2] ) & 0x3f ) )
            );

            if ( $charCode < 2048 )
            {
                throw new RuntimeException( 'Illegal UTF-8 input character: ' . $char );
            }
        }
        // 21 bits, 4 chars
        else if ( ( ord( $char[0] ) & 0xf8 ) == 0xf0 )
        {
            if ( ( ord( $char[1] ) & 0xc0 ) != 0x80 ||
                 ( ord( $char[2] ) & 0xc0 ) != 0x80 ||
                 ( ord( $char[3] ) & 0xc0 ) != 0x80 )
            {
                return $charCode;
            }

            $charCode = (
                ( ( ord( $char[0] ) & 0x07 ) << 18 ) +
                ( ( ord( $char[1] ) & 0x3f ) << 12 ) +
                ( ( ord( $char[2] ) & 0x3f ) << 6 ) +
                ( ( ord( $char[3] ) & 0x3f ) )
            );

            if ( $charCode < 65536 )
            {
                throw new RuntimeException( 'Illegal UTF-8 input character: ' . $char );
            }
        }
        // 26 bits, 5 chars
        else if ( ( ord( $char[0] ) & 0xfc ) == 0xf8 )
        {
            if ( ( ord( $char[1] ) & 0xc0 ) != 0x80 ||
                 ( ord( $char[2] ) & 0xc0 ) != 0x80 ||
                 ( ord( $char[3] ) & 0xc0 ) != 0x80 ||
                 ( ord( $char[4] ) & 0xc0 ) != 0x80 )
            {
                return $charCode;
            }

            $charCode = (
                ( ( ord( $char[0] ) & 0x03 ) << 24 ) +
                ( ( ord( $char[1] ) & 0x3f ) << 18 ) +
                ( ( ord( $char[2] ) & 0x3f ) << 12 ) +
                ( ( ord( $char[3] ) & 0x3f ) << 6 ) +
                ( ( ord( $char[4] ) & 0x3f ) )
            );

            if ( $charCode < 2097152 )
            {
                throw new RuntimeException( 'Illegal UTF-8 input character: ' . $char );
            }
        }
        // 31 bits, 6 chars
        else if ( ( ord( $char[0] ) & 0xfe ) == 0xfc )
        {
            if ( ( ord( $char[1] ) & 0xc0 ) != 0x80 ||
                 ( ord( $char[2] ) & 0xc0 ) != 0x80 ||
                 ( ord( $char[3] ) & 0xc0 ) != 0x80 ||
                 ( ord( $char[4] ) & 0xc0 ) != 0x80 ||
                 ( ord( $char[5] ) & 0xc0 ) != 0x80 )
            {
                return $charCode;
            }

            $charCode = (
                ( ( ord( $char[0] ) & 0x01 ) << 30 ) +
                ( ( ord( $char[1] ) & 0x3f ) << 24 ) +
                ( ( ord( $char[2] ) & 0x3f ) << 18 ) +
                ( ( ord( $char[3] ) & 0x3f ) << 12 ) +
                ( ( ord( $char[4] ) & 0x3f ) << 6 ) +
                ( ( ord( $char[5] ) & 0x3f ) )
            );

            if ( $charCode < 67108864 )
            {
                throw new RuntimeException( 'Illegal UTF-8 input character: ' . $char );
            }
        }

        return $charCode;
    }
}

