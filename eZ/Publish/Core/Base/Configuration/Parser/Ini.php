<?php
/**
 * File contains Configuration Ini Parser / writer
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 * @uses ezcConfiguration As fallback if parse_ini_string() fails
 */

namespace eZ\Publish\Core\Base\Configuration\Parser;

use eZ\Publish\Core\Base\Configuration;
use eZ\Publish\Core\Base\Configuration\Parser;
use ezcConfiguration;
use ezcConfigurationIniReader;
use LogicException;

/**
 * Configuration Ini Parser / writer
 */
class Ini implements Parser
{
    /**
     * Constant string used as a temporary true variable during ini parsing to avoid
     * parse_ini_file from casting it to 1
     *
     * @var string
     */
    const TEMP_INI_TRUE_VAR = '__TRUE__';

    /**
     * Constant string used as a temporary false variable during ini parsing to avoid
     * parse_ini_file from casting it to 0
     *
     * @var string
     */
    const TEMP_INI_FALSE_VAR = '__FALSE__';

    /**
     * Constant string used as a temporary array key separator when merging several dimensions
     * for php_ini_string support, {@see parsePhpPostArrayFilter()} & {@see parserPhpDimensionArraySupport()}
     *
     * @var string
     */
    const TEMP_INI_KEY_VAR = '__KEY__';

    /**
     * Constant string used as a temporary quote value when using php_ini_string {@see parserPhpQuoteSupport()}
     *
     * @var string
     */
    const TEMP_INI_QUOTE_VAR = '__QUOTE__';

    /**
     * Defines if strict mode should be used (parse_ini_string), otherwise use ezcConfigurationIniReader
     *
     * @var boolean
     */
    protected $strictMode = true;

    /**
     * @var int
     */
    protected $filePermission = 0644;

    /**
     * @var int
     */
    protected $dirPermission = 0755;

    /**
     * Construct an instance of Ini Parser
     *
     * @param array $settings
     */
    public function __construct( array $settings )
    {
        if ( isset( $settings['IniParserStrict'] ) )
            $this->strictMode = $settings['IniParserStrict'];

        if ( isset( $settings['CacheFilePermission'] ) )
            $this->filePermission = $settings['CacheFilePermission'];

        if ( isset( $settings['CacheDirPermission'] ) )
            $this->dirPermission = $settings['CacheDirPermission'];
    }

    /**
     * Parse file and return raw configuration data
     *
     * @todo Change impl to use exceptions instead of trigger_error in most cases
     *
     * @param string $fileName A valid file name
     * @param string $fileContent
     *
     * @return array
     */
    public function parse( $fileName, $fileContent )
    {
        if ( !$this->strictMode )
        {
            return $this->parseFileEzc( $fileContent );
        }

        $configurationData = $this->parseFilePhp( $fileContent );
        if ( $configurationData === false )
        {
            trigger_error(
                "parse_ini_string( {$fileName} ) failed, see warning for line number. ",
                E_USER_NOTICE
            );
            return array();
        }
        return $configurationData;
    }

    /**
     * Parse configuration file using parse_ini_string (only supported on php 5.3 and up)
     *
     * This parser is stricter then ezcConfigurationIniReader and does not support many of
     * the ini files eZ Publish use because things like regex as ini variable and so on.
     *
     * @param string $fileContent
     *
     * @return array|false Data structure for parsed ini file or false if it fails
     */
    protected function parseFilePhp( $fileContent )
    {
        // First some pre processing to normalize result with ezc result (avoid 'true' becoming '1')
        $fileContent = str_replace(
            array( '#', "\r\n", "\r", "=true\n", "=false\n" ),
            array( ';', "\n", "\n", "=" . self::TEMP_INI_TRUE_VAR . "\n", "=" . self::TEMP_INI_FALSE_VAR . "\n" ),
            $fileContent . "\n"
        );
        $fileContent = $this->parserPhpDimensionArraySupport( $fileContent );
        $fileContent = $this->parserClearArraySupport( $fileContent );
        $fileContent = $this->parserPhpQuoteSupport( $fileContent );

        // Parse string
        $configurationData = parse_ini_string( $fileContent, true );

        // Post processing to turn en/disabled back to bool values (like ezc parser does for true/false strings)
        // cast numeric values and unset array self::TEMP_INI_UNSET_VAR values as set in {@link self::parserClearArraySupport()}
        if ( $configurationData === false )
        {
            return $configurationData;
        }

        foreach ( $configurationData as $section => $sectionArray )
        {
            foreach ( $sectionArray as $setting => $settingValue )
            {
                if ( is_array( $settingValue ) )
                {
                    $configurationData[$section][$setting] = self::parsePhpPostArrayFilter( $configurationData[$section][$setting] );
                }
                else
                {
                    $configurationData[$section][$setting] = self::parsePhpPostFilter( $settingValue );
                }
            }
        }
        return $configurationData;
    }

    /**
     * Parse configuration file using ezcConfigurationIniReader
     *
     * @todo Change impl to use exceptions instead of trigger_error
     *
     * @param string $fileContent
     *
     * @return array Data structure for parsed ini file
     */
    protected function parseFileEzc( $fileContent )
    {
        // First some pre processing to normalize result with parse_ini_string result
        $fileContent = str_replace( array( "\r\n", "\r" ), "\n", $fileContent . "\n" );
        $fileContent = preg_replace( array( '/^<\?php[^\/]\/\*\s*/', '/\*\/[^\?]\?>/' ), '', $fileContent );
        $fileContent = preg_replace_callback(
            '/\n\[(\w+):[^\]]+\]\n/',
            function ( $matches )
            {
                // replace ':' in section names as it is not supported by ezcConfigurationIniReader
                return str_replace( ':', '__EXT__', $matches[0] );
            },
            $fileContent
        );
        $fileContent = $this->parserClearArraySupport( $fileContent );

        // Create ini dir if it does not exist
        if ( !is_dir( Configuration::CONFIG_CACHE_DIR ) )
            mkdir( Configuration::CONFIG_CACHE_DIR, $this->dirPermission, true );

        // Create temp file
        $tempFileName = Configuration::CONFIG_CACHE_DIR . 'temp-' . mt_rand() . '.tmp.ini';
        if ( file_put_contents( $tempFileName, $fileContent ) === false )
        {
            trigger_error( __METHOD__ . ": temporary ini file ($tempFileName) needed for ini parsing not writable!", E_USER_WARNING );
            return array();
        }

        // Parse string
        try
        {
            $reader = new ezcConfigurationIniReader( $tempFileName );
            $configuration = $reader->load();
        }
        catch ( Exception $e)
        {
            trigger_error( __METHOD__ . ': Caught exception: ' .  $e->getMessage() . " \n[" . $e->getFile() . ' (' . $e->getLine() . ')]', E_USER_WARNING );
        }

        $configurationData = array();
        $result = $reader->validate();
        if ( !$result->isValid )
        {
            foreach ( $result->getResultList() as $resultItem )
            {
                 trigger_error( __METHOD__ . ': ezc parser error in ' . $resultItem->file . ':' . $resultItem->line . ':' . $resultItem->column. ': ' . $resultItem->details, E_USER_WARNING );
            }
        }
        else if ( $configuration instanceof ezcConfiguration )
        {
            $configurationData = $configuration->getAllSettings();
            foreach ( $configurationData as $section => $sectionArray )
            {
                // Fix : in section name
                if ( stripos( $section, '__EXT__' ) !== false )
                {
                    unset( $configurationData[$section] );
                    $section = str_replace( '__EXT__', ':', $section );
                    $configurationData[$section] = $sectionArray;
                }

                foreach ( $sectionArray as $setting => $value )
                {
                    // fix appending ##! and such lines
                    if ( isset( $value[0] ) && is_string( $value ) && strpos( $value, '#' ) !== false )
                    {
                        $value = explode( '#', $value );
                        $configurationData[$section][$setting] = $value[0];
                    }
                }
            }
        }
        else
        {
            trigger_error( __METHOD__ . ': $configuration not instanceof ezcConfiguration', E_USER_WARNING );
        }
        // Remove temp file
        unlink( $tempFileName );
        return $configurationData;
    }

    /**
     * Pre processing needed for php ini parsers to support three dimensional arrays
     *
     * Injects constants which is later cleaned up in {@link parsePhpPostArrayFilter()}.
     *
     * @param string $fileContent
     *
     * @return string
     */
    protected function parserPhpDimensionArraySupport( $fileContent )
    {
        if ( preg_match_all( "/^([\w_-]+)\[([\w_-]+)?\]\[([\w_-]+)?\](\[([\w_-]+)?\])?(\[([\w_-]+)?\])?(\[([\w_-]+)?\])?/m", $fileContent, $valueArray, PREG_OFFSET_CAPTURE ) )
        {
            $offsetDiff = 0;// Since we use offset captured before replace operations, we need to maintain an offset diff
            foreach ( $valueArray[0] as $key => $match )
            {
                // Variable name
                $replaceString = $valueArray[1][$key][0] . '[';

                // If first key is empty use $key to make it unique
                if ( empty( $valueArray[2][$key][0] ) )
                    $replaceString .= $key;
                else
                    $replaceString .= $valueArray[2][$key][0];

                // Add key separator
                $replaceString .= self::TEMP_INI_KEY_VAR;

                // If second key is empty use $key to make it unique
                if ( empty( $valueArray[3][$key][0] ) )
                    $replaceString .= $key;
                else
                    $replaceString .= $valueArray[3][$key][0];

                if ( !empty( $valueArray[4][$key][0] ) )
                {
                    $replaceString .= self::TEMP_INI_KEY_VAR;
                    if ( empty( $valueArray[5][$key][0] ) )
                        $replaceString .= $key;
                    else
                        $replaceString .= $valueArray[5][$key][0];
                }

                if ( !empty( $valueArray[6][$key][0] ) )
                {
                    $replaceString .= self::TEMP_INI_KEY_VAR;
                    if ( empty( $valueArray[7][$key][0] ) )
                        $replaceString .= $key;
                    else
                        $replaceString .= $valueArray[7][$key][0];
                }

                if ( !empty( $valueArray[8][$key][0] ) )
                {
                    $replaceString .= self::TEMP_INI_KEY_VAR;
                    if ( empty( $valueArray[9][$key][0] ) )
                        $replaceString .= $key;
                    else
                        $replaceString .= $valueArray[9][$key][0];
                }

                $replaceString .= ']';

                $fileContent = substr_replace( $fileContent, $replaceString, $match[1] + $offsetDiff, strlen( $match[0] ) );
                $offsetDiff += strlen( $replaceString ) - strlen( $match[0] );
            }
        }
        return $fileContent;
    }
    /**
     * Pre processing needed for php ini parsers to support quotes in values
     *
     * Injects constants which is later cleaned up in {@link parsePhpPostArrayFilter()}.
     *
     * @param string $fileContent
     *
     * @return string
     */
    protected function parserPhpQuoteSupport( $fileContent )
    {
        if ( preg_match_all( "/^([^=]+)=([^'\n]+)?'(([^'\n]+)'(([^'\n]+)')?)?/m", $fileContent, $valueArray, PREG_OFFSET_CAPTURE ) )
        {
            $offsetDiff = 0;// Since we use offset captured before replace operations, we need to maintain an offset diff
            foreach ( $valueArray[0] as $key => $match )
            {
                // Variable name
                $replaceString = $valueArray[1][$key][0] . '=';

                // If first key is empty use $key to make it unique
                if ( !empty( $valueArray[2][$key][0] ) )
                    $replaceString .= $valueArray[2][$key][0];

                $replaceString .= self::TEMP_INI_QUOTE_VAR;

                if ( !empty( $valueArray[4][$key][0] ) )
                {
                    $replaceString .= $valueArray[4][$key][0];
                    $replaceString .= self::TEMP_INI_QUOTE_VAR;
                }

                if ( !empty( $valueArray[6][$key][0] ) )
                {
                    $replaceString .= $valueArray[6][$key][0];
                    $replaceString .= self::TEMP_INI_QUOTE_VAR;
                }

                $fileContent = substr_replace( $fileContent, $replaceString, $match[1] + $offsetDiff, strlen( $match[0] ) );
                $offsetDiff += strlen( $replaceString ) - strlen( $match[0] );
            }
        }
        return $fileContent;
    }

    /**
     * Common pre processing needed for both ezc and php parsers
     *
     * Marks array clearing, so post parser code in {@link Configuration::parse()} can detect it
     *
     * @param string $fileContent
     *
     * @return string
     */
    protected function parserClearArraySupport( $fileContent )
    {
        if ( preg_match_all( "/^([\w_-]+)\[([\w_-]+)?\](\[([\w_-]+)?\])?$/m", $fileContent, $valueArray ) )
        {
            foreach ( $valueArray[0] as $variableArrayClearing )
            {
                $fileContent = str_replace( "\n$variableArrayClearing\n", "\n$variableArrayClearing=" . Configuration::TEMP_INI_UNSET_VAR . "\n", $fileContent );
            }
        }
        return $fileContent;
    }

    /**
     * Transform temporary values the php equivalent to make sure parsed ini settings
     * are the same as with ezcConfigurationIniReader.
     *
     * @param mixed $iniValue
     *
     * @return mixed
     */
    protected static function parsePhpPostFilter( $iniValue )
    {
        if ( $iniValue === self::TEMP_INI_TRUE_VAR )
            return true;

        if ( $iniValue === self::TEMP_INI_FALSE_VAR )
            return false;

        if ( is_numeric( $iniValue ) )
        {
            if ( strpos( $iniValue, '.' ) !== false )
                return (float)$iniValue;

            return (int)$iniValue;
        }

        if ( $iniValue === self::TEMP_INI_QUOTE_VAR )
            return '\'';

        if ( strpos( $iniValue, self::TEMP_INI_QUOTE_VAR ) !== false )
            $iniValue = str_replace( self::TEMP_INI_QUOTE_VAR, '\'', $iniValue );

        if ( isset( $iniValue[1] ) && is_string( $iniValue ) )
            return rtrim( $iniValue, ' ' );

        return $iniValue;
    }

    /**
     * Transform temporary array values the php equivalent to make sure parsed ini settings
     * are the same as with ezcConfigurationIniReader.
     *
     * Deals specifically with post parse fixes for three dimensional arrays.
     *
     * @param array $array
     *
     * @return array
     */
    protected static function parsePhpPostArrayFilter( array $array )
    {
        $newArray = array();
        foreach ( $array as $key => $value )
        {
            if ( strpos( $key, self::TEMP_INI_KEY_VAR ) !== false )
            {
                $subKeys = explode( self::TEMP_INI_KEY_VAR, $key );
                $value = self::parsePhpPostFilter( $value );
                foreach ( array_reverse( $subKeys ) as $subKey )
                {
                    if ( is_numeric( $subKey[0] ) )
                        $value = array( $value );
                    else
                        $value = array( $subKey => $value );
                }

                $newArray = array_merge_recursive( $newArray, $value );
            }
            else
            {
                $newArray[ $key ] = self::parsePhpPostFilter( $value );
            }
        }
        return $newArray;
    }

    /**
     * Store raw configuration data to file
     *
     * @see eZ\Publish\Core\Base\Configuration\Parser::parse() For $configurationData definition
     * @todo Test..
     * @param string $fileName A valid file name, will be overwritten if it exists
     * @param array $configurationData
     */
    public function write( $fileName, array $configurationData )
    {
        if ( !is_writable( $fileName ) )
        {
            throw new LogicException( "{$fileName} is not writable", "can not save configuration data!" );
        }

        if ( strpos( $fileName, '.php', 1 ) !== false )
        {
            $iniStr = "<?php /* #?ini charset=\"utf-8\"?\n";
        }
        else
        {
            $iniStr = "#?ini charset=\"utf-8\"?\n";
        }

        foreach ( $configurationData as $section => $sectionData )
        {
            $iniStr .= "\n\n[{$section}]";
            foreach ( $sectionData as $var => $value )
            {
                if ( $value === true )
                {
                    $iniStr .= "\n{$var}=true";
                }
                else if ( $value === false )
                {
                    $iniStr .= "\n{$var}=false";
                }
                else if ( is_array( $value ) )
                {
                    if ( empty( $value ) )
                    {
                        $iniStr .= "\n{$var}[]";
                        continue;
                    }
                    foreach ( $value as $arrayKey => $arrayValue )
                    {
                        if ( $arrayValue === Configuration::TEMP_INI_UNSET_VAR )
                        {
                            $iniStr .= "\n{$var}[]";
                        }
                        else if ( is_string( $arrayKey ) )
                        {
                            $iniStr .= "\n{$var}[{$arrayKey}]={$arrayValue}";
                        }
                        else
                        {
                            $iniStr .= "\n{$var}[]={$arrayValue}";
                        }
                    }
                }
            }
        }
        file_put_contents( $fileName, $iniStr, LOCK_EX );
    }
}
