<?php
/**
 * File contains Configuration Ini Parser / writer
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 *
 * @uses \ezcConfiguration As fallback if parse_ini_string() fails
 */

namespace ezp\base\Configuration;

/**
 * Configuration Ini Parser / writer
 *
 * @package ezp
 * @subpackage base
 */
use \ezp\base\Configuration;
class IniParser implements \ezp\base\ConfigurationParserInterface, \ezp\base\ConfigurationWriterInterface
{
    /**
     * Constant string used as a temporary true variable during ini parsing to avoid
     * parese_ini_file from casting it to 1
     *
     * @var string
     */
    const TEMP_INI_TRUE_VAR = '__TRUE__';

    /**
     * Constant string used as a temporary false variable during ini parsing to avoid
     * parese_ini_file from casting it to 0
     *
     * @var string
     */
    const TEMP_INI_FALSE_VAR = '__FALSE__';

    /**
     * File name as needed by writer
     *
     * @var string
     */
    protected $file;

    /**
     * Construct an instance for a specific file
     *
     * @param string $file A valid file name, file must exist!
     */
    public function __construct( $file )
    {
        $this->file = $file;
    }

    /**
     * Parse file and return raw configuration data
     *
     * @param string $fileContent
     * @return array
     */
    public function parse( $fileContent )
    {
        $configurationData = $this->parseFilePhp( $fileContent );
        // if it failed, fallback to ezc ini parser for compatibility
        if ( $configurationData === false )
        {
            $configurationData = $this->parseFileEzc( $fileContent );
        }
        return $configurationData;
    }

    /**
     * Parse configuration file using parse_ini_string (only supported on php 5.3 and up)
     *
     * This parser is stricter then ezcConfigurationIniReader and does not support many of
     * the ini files eZ Publish use because things like regex as ini variable and so on.
     *
     * @access internal
     * @param string $fileContent
     * @return array|false Data structure for parsed ini file or false if it fails
     */
    public function parseFilePhp( $fileContent )
    {
        // First some pre processing to normalize result with ezc result (avoid 'true' becoming '1')
        $fileContent = str_replace( array( '#', "\r\n", "\r", "=true\n", "=false\n" ),
                                    array( ';', "\n", "\n", "=" . self::TEMP_INI_TRUE_VAR . "\n", "=" . self::TEMP_INI_FALSE_VAR . "\n" ),
                                    $fileContent . "\n" );
        $fileContent = self::parserClearArraySupport( $fileContent );

        // Parse string
        $configurationData = parse_ini_string( $fileContent, true );

        // Post processing to turn en/disabled back to bool values (like ezc parser does for true/false strings)
        // cast numeric values and unset array self::TEMP_INI_UNSET_VAR values as set in {@link self::parserClearArraySupport()}
        if ( $configurationData !== false )
        {
            foreach( $configurationData as $section => $sectionArray )
            {
                foreach( $sectionArray as $setting => $settingValue )
                {

                    if ( is_array( $settingValue ) )
                    {
                        foreach( $settingValue as $key => $keyValue )
                        {
                            $configurationData[$section][$setting][$key] = self::parseFilePhpPostFilter( $keyValue );
                        }
                    }
                    else
                    {
                        $configurationData[$section][$setting] = self::parseFilePhpPostFilter( $settingValue );
                    }
                }
            }
        }
        return $configurationData;
    }

    /**
     * Parse configuration file using ezcConfigurationIniReader
     *
     * @access internal
     * @param string $fileContent
     * @return array Data structure for parsed ini file
     */
    public function parseFileEzc( $fileContent )
    {
        // First some pre processing to normalize result with parse_ini_string result
        $fileContent = str_replace( array( "\r\n", "\r" ), array( "\n", "\n" ), $fileContent . "\n" );
        $fileContent = preg_replace( array( '/^<\?php[^\/]\/\*\s*/', '/\*\/[^\?]\?>/' ), '', $fileContent );
        $fileContent = self::parserClearArraySupport( $fileContent );

        // Create ini dir if it does not exist
        if ( !file_exists( Configuration::CONFIG_CACHE_DIR ) )
            mkdir( Configuration::CONFIG_CACHE_DIR, Configuration::$filePermission, true );

        // Create temp file
        $tempFileName = Configuration::CONFIG_CACHE_DIR . 'temp-' . mt_rand() . '.tmp.ini';
        $res = file_put_contents( $tempFileName, $fileContent );
        if ( $res === false )
        {
            trigger_error( __METHOD__ . ": temporary ini file ($tempFileName) needed for ini parsing not writable!", E_USER_WARNING );
            return array();
        }

        // Parse string
        try
        {
            $reader = new \ezcConfigurationIniReader( $tempFileName );
            $cfg = $reader->load();
        }
        catch ( Exception $e)
        {
            trigger_error( __METHOD__ . ': Caught exception: ' .  $e->getMessage() . " \n[" . $e->getFile() . ' (' . $e->getLine() . ')]', E_USER_WARNING );
        }

        $configurationData = array();
        $result            = $reader->validate();
        if ( !$result->isValid )
        {
            foreach ( $result->getResultList() as $resultItem )
            {
                 trigger_error( __METHOD__ . ': ezc parser error in ' . $resultItem->file . ':' . $resultItem->line . ':' . $resultItem->column. ': ' . $resultItem->details, E_USER_WARNING );
            }

        }
        else if ( $cfg instanceof \ezcConfiguration )
        {
            $configurationData = $cfg->getAllSettings();
            foreach( $configurationData as $section => $sectionArray )
            {
                foreach( $sectionArray as $setting => $value )
                {
                    // fix appending ##! and such lines
                    if ( isset( $value[0] ) && is_string( $value ) && strpos( $value, '#') !== false )
                    {
                        $value = explode( '#', $value );
                        $configurationData[$section][$setting] = $value[0];
                    }
                }
            }
        }
        else
        {
            trigger_error( __METHOD__ . ': $cfg not instanceof ezcConfiguration', E_USER_WARNING );
        }
        // Remove temp file
        unlink( $tempFileName );
        return $configurationData;
    }

    /**
     * Transform temporary values the php equivalent to make sure parsed ini settings
     * are the same as with ezcConfigurationIniReader.
     *
     * @param mixed $iniValue
     * @return mixed
     */
    protected static function parseFilePhpPostFilter( $iniValue )
    {
        if ( $iniValue === self::TEMP_INI_TRUE_VAR )
            return true;
        else if ( $iniValue === self::TEMP_INI_FALSE_VAR )
            return false;
        else if ( is_numeric( $iniValue ) )
        {
            if ( strpos( $iniValue, '.' ) !== false )
                return (float) $iniValue;
            else
                return (int) $iniValue;
        }
        else if ( isset( $iniValue[1] ) && is_string( $iniValue ) )
            return rtrim( $iniValue, ' ' );
        return $iniValue;
    }

    /**
     * Common pre processing needed for both ezc and php parsers
     * Marks array clearing, so post parser code in {@link Configuration::parse()} can detect it
     *
     * @param string $fileContent
     * @return string
     */
    protected static function parserClearArraySupport( $fileContent )
    {
        if ( preg_match_all("/\n([\w_-]+)\[\]\n/", $fileContent, $valueArray ) )
        {
            foreach( $valueArray[1] as $variableArrayClearing )
            {
                $variableArrayClearing .= '[]';
                $fileContent = str_replace( "\n$variableArrayClearing\n", "\n$variableArrayClearing=" . Configuration::TEMP_INI_UNSET_VAR . "\n" , $fileContent );
            }
        }

        // For some reason this needs to be done twice to get them all
        if ( preg_match_all("#\n([\w_-]+)\[\]\n#", $fileContent, $valueArray ) )
        {
            trigger_error( __METHOD__ . ": regex second round!", E_USER_NOTICE );
            foreach( $valueArray[1] as $variableArrayClearing )
            {
                $variableArrayClearing .= '[]';
                $fileContent = str_replace( "\n$variableArrayClearing\n", "\n$variableArrayClearing=" . Configuration::TEMP_INI_UNSET_VAR . "\n" , $fileContent );
            }
        }
        return $fileContent;
    }

    /**
     * Store raw configuration data to file
     *
     * @see \ezp\base\Interfaces\Configuration\Parser::parse() For $configurationData definition
     * @todo Test..
     * @param array $configurationData
     */
    public function write( array $configurationData )
    {
        if ( !is_writable( $this->file) )
        {
            throw new \LogicException( __METHOD__ . ": {$this->file} is not writable, can not save configuration data!" );
        }

        if ( strpos( $this->file, '.php', 1 ) !== false )
        {
            $iniStr = "<?php /* #?ini charset=\"utf-8\"?\n";
        }
        else
        {
            $iniStr = "#?ini charset=\"utf-8\"?\n";
        }

        foreach( $configurationData as $section => $sectionData )
        {
            $iniStr .= "\n\n[{$section}]";
            foreach( $sectionData as $var => $value )
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
                    foreach( $value as $arrayKey => $arrayValue )
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
        file_put_contents( $this->file, $iniStr, LOCK_EX );
    }
}

?>