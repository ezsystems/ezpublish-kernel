<?php
/**
 * File containing the \eZ\Publish\Core\FieldType\XmlText\Input\Parser\Base
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Input\Parser;

use eZ\Publish\Core\FieldType\XmlText\Schema as XmlSchema,
    eZ\Publish\Core\FieldType\XmlText\Input\Handler as InputHandler,
    eZ\Publish\Core\Base\Exceptions\BadConfiguration,
    DOMDocument,
    DOMElement,
    DOMNode,
    DOMText;

/**
 * Base class for the input parser.
 * The goal of the parser is XML/HTML analyzing, fixing and transforming.
 * The input is processed in 2 passes:
 *  - 1st pass: Parsing input, check for syntax errors, build DOM tree.
 *  - 2nd pass: Walking through DOM tree, checking validity by XML schema,
 *              calling tag handlers to transform the tree.
 *
 * Both passes are controlled by the arrays described bellow and user handler functions.
 */
abstract class Base
{
    /**
     * Error types constants
     */
    const ERROR_NONE = 0;
    const ERROR_SYNTAX = 4;
    const ERROR_SCHEMA = 8;
    const ERROR_DATA = 16;
    const ERROR_ALL = 28; // 4+8+16

    /**
     * Parser options constants, to be used with setOption
     *
     * @var string
     * @see setOption()
     */
    const OPT_VALIDATE_ERROR_LEVEL = 'ValidateErrorLevel';
    const OPT_DETECT_ERROR_LEVEL = 'DetectErrorLevel';
    const OPT_REMOVE_DEFAULT_ATTRS = 'RemoveDefaultAttrs';
    const OPT_PARSE_LINE_BREAKS = 'ParseLineBreaks';
    const OPT_TRIM_SPACES = 'TrimSpaces';
    const OPT_ALLOW_MULTIPLE_SPACES = 'AllowMultipleSpaces';
    const OPT_ALLOW_NUMERIC_ENTITIES = 'AllowNumericEntities';
    const OPT_STRICT_HEADERS = 'StrictHeaders';
    const OPT_CHECK_EXTERNAL_DATA = 'checkExternalData';

    /**
     * Properties of elements that come from the input.
     *
     * Each array element describes a tag that comes from the input. Arrays index is
     * a tag's name. Each element is an array that may contain the following members:
     * - name: a string representing a new name of the tag
     * - nameHandler: a name of the function that returns new tag name.
     *                Function format: function tagNameHandler( $tagName, &$attributes )
     *                If none of those elements are defined the original tag's name is used.
     * - noChildren: boolean value that determines if this tag could have child tags. Default value is false.
     *
     * <code>
     * $InputTags = array(
     *     'original-name' => array( 'name' => 'new-name' ),
     *     'original-name2' => array( 'nameHandler' => 'tagNameHandler',
     *                                'noChildren' => true ),
     * );
     * </code>
     */
    protected $InputTags = array();

    /**
     * Properties of elements that are produced in the output.
     *
     * Each array element describes a tag presented in the output. Arrays index is
     * a tag's name. Each element is an array that may contain the following members:
     * - parsingHandler: "Parsing handler" called at parse pass 1 before processing tag's children.
     * - initHandler: "Init handler" called at pass 2 before proccessing tag's children.
     * - structHandler: "Structure handler" called at pass 2 after proccessing tag's children,
     *                  but before schema validity check. It can be used to implement structure
     *                  transformations.
     * - publishHandler: "Publish handler" called at pass 2 after schema validity check, so it is called
     *                  in case the element has it's guaranteed place in the DOM tree.
     * - attributes: an array that describes attributes transformations. Array's index is the
     *              original name of an attribute, and the value is the new name.
     * - requiredInputAttributes: attributes that are required in the input tag. If they are not presented
     *                            it raises invalid input flag.
     *
     * <code>
     * public $OutputTags = array(
     *    'custom' => array( 'parsingHandler' => 'parsingHandlerCustom',
     *                          'initHandler' => 'initHandlerCustom',
     *                          'structHandler' => 'structHandlerCustom',
     *                          'publishHandler' => 'publishHandlerCustom',
     *                          'attributes' => array( 'title' => 'name' ) ),
     * );
     *
     * @var array
     */
    protected $OutputTags = array();

    /**
     * List of XmlText namespaces
     *
     * @var array
     */
    protected $Namespaces = array( 'image' => 'http://ez.no/namespaces/ezpublish3/image/',
                                   'xhtml' => 'http://ez.no/namespaces/ezpublish3/xhtml/',
                                   'custom' => 'http://ez.no/namespaces/ezpublish3/custom/',
                                   'tmp' => 'http://ez.no/namespaces/ezpublish3/temporary/' );

    /**
     * Parser options list
     *
     * @var mixed[string]
     */
    protected $options = array(
        self::OPT_VALIDATE_ERROR_LEVEL => self::ERROR_NONE,
        self::OPT_DETECT_ERROR_LEVEL => self::ERROR_NONE,
        self::OPT_REMOVE_DEFAULT_ATTRS => false,
        self::OPT_PARSE_LINE_BREAKS => false,
        self::OPT_TRIM_SPACES => false,
        self::OPT_ALLOW_MULTIPLE_SPACES => false,
        self::OPT_ALLOW_NUMERIC_ENTITIES => false,
        self::OPT_STRICT_HEADERS => false,
        self::OPT_CHECK_EXTERNAL_DATA => true,
    );

    /**
     * XmlSchema object
     *
     * @var \eZ\Publish\Core\FieldType\XmlText\Schema
     */
    protected $XMLSchema;

    /**
     * DOM document object
     *
     * @var \DOMDocument
     */
    protected $Document = null;

    /**
     * Processing messages
     *
     * @var string[]
     * @see getMessages()
     */
    protected $Messages = array();

    /**
     * Parent nodes stack
     *
     * @var string[]
     */
    protected $ParentStack = array();

    /**
     * Boolean holding the validity status of the input string
     *
     * @var boolean
     * @see isInputValid()
     */
    protected $isInputValid = true;

    /**
     * Boolean used to interrupt the process between steps
     *
     * @var boolean
     */
    protected $QuitProcess = false;

    /**
     * Array of Url objects ids
     *
     * @var integer[]
     */
    protected $urlIDArray = array();

    /**
     * Array of related Content objects id
     *
     * @var integer[]
     */
    protected $relatedObjectIDArray = array();

    /**
     * Array of linked Content objects id
     *
     * @var integer[]
     */
    protected $linkedObjectIDArray = array();

    // needed for self-embedding protection
    protected $contentObjectID = 0;

    /**
     * Input handler
     *
     * @var \eZ\Publish\Core\FieldType\XmlText\Input\Handler
     */
    protected $handler;

    /**
     * Construct a new Parser
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Schema $scheme
     * @param array $options
     */
    public function __construct( XmlSchema $scheme, array $options = array() )
    {
        $this->XMLSchema = $scheme;

        // Set options
        $this->options = $options + $this->options;
    }

    /**
     * Sets the input handler for the parser to $handler
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Input\Handler $handler
     */
    public function setHandler( InputHandler $handler )
    {
        $this->handler = $handler;
    }

    /**
     * Sets the parser option $option to $value
     *
     * @param string $option One of self::OPT_*
     * @param mixed $value
     * @throws \eZ\Publish\Core\Base\Exceptions\BadConfiguration If the option is unknown or the value incorrect
     */
    public function setOption( $option, $value )
    {
        if ( !$this->optionExists( $option ) )
        {
            throw new BadConfiguration( "Unknown option $option" );
        }
        // @todo Add control over value
        $this->options[$option] = $value;
    }

    /**
     * Gets the parser option $option
     *
     * @param string $option One of self::OPT_*
     * @throws \eZ\Publish\Core\Base\Exceptions\BadConfiguration If the option is unknown or the value incorrect
     */
    public function getOption( $option )
    {
        if ( !$this->optionExists( $option ) )
        {
            throw new BadConfiguration( "Unknown option $option" );
        }
        return $this->options[$option];
    }

    /**
     * Check if $option exists
     *
     * @param string $option
     * @return bool
     */
    private function optionExists( $option )
    {
        return isset( $this->options[$option] );
    }

    /**
     * Processes $text
     *
     * @param string $text
     * @param bool $createRootNode
     * @return DOMDocument
     */
    public function process( $text, $createRootNode = true )
    {
        $text = str_replace( "\r", '', $text );
        $text = str_replace( "\t", ' ', $text );
        // replace unicode chars that will break the XML validity
        // see http://www.w3.org/TR/REC-xml/#charsets
        $text = preg_replace( '/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $text, -1, $count );
        if ( $count > 0 )
        {
            $this->messages[] = "$count invalid character(s) have been found and replaced by a space";
        }
        if ( !$this->getOption( self::OPT_PARSE_LINE_BREAKS ) )
        {
            $text = str_replace( "\n", '', $text);
        }

        $this->Document = $this->createDomDocument();

        // Perform pass 1
        // Parsing the source string
        $this->performPass1( $text );

        $this->Document->formatOutput = true;
        // $debug = eZDebugSetting::isConditionTrue( 'kernel-datatype-ezxmltext', eZDebug::LEVEL_DEBUG );
        /*if ( $debug )
        {
            // eZDebug::writeDebug( $this->Document->saveXML(), eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext', 'XML after pass 1' ) );
        }*/

        if ( $this->QuitProcess )
        {
            return false;
        }

        // Perform pass 2
        $this->performPass2();

        $this->Document->formatOutput = true;
        /*if ( $debug )
        {
            // eZDebug::writeDebug( $this->Document->saveXML(), eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext', 'XML after pass 2' ) );
        }*/

        if ( $this->QuitProcess )
        {
            return false;
        }

        return $this->Document;
    }

    /**
     * Creates the DOMDocument object holding the XML text
     *
     * @param bool $createRootNode wether or not to create the root <section> node
     * @return \DOMDocument
     */
    protected function createDomDocument( $createRootNode = true )
    {
        $domDocument = new DOMDocument( '1.0', 'utf-8' );

        // Creating root section with namespaces definitions
        if ( $createRootNode )
        {
            $mainSection = $domDocument->createElement( 'section' );
            $domDocument->appendChild( $mainSection );
            foreach ( array( 'image', 'xhtml', 'custom' ) as $prefix )
            {
                $mainSection->setAttributeNS( 'http://www.w3.org/2000/xmlns/', 'xmlns:' . $prefix, $this->Namespaces[$prefix] );
            }
        }
        return $domDocument;
    }

    /*
     * Pass 1: Parsing the source HTML string.
     */
    private function performPass1( &$data )
    {
        $ret = true;
        $pos = 0;

        if ( $this->Document->documentElement )
        {
            do
            {
                $this->parseTag( $data, $pos, $this->Document->documentElement );
                if ( $this->QuitProcess )
                {
                    $ret = false;
                    break;
                }

            }
            while ( $pos < strlen( $data ) );
        }
        else
        {
            $tmp = null;
            $this->parseTag( $data, $pos, $tmp );
            if ( $this->QuitProcess )
            {
                $ret = false;
            }
        }
        return $ret;
    }

    private function parseTag( &$data, &$pos, &$parent )
    {
        // Find tag, determine it's type, name and attributes.
        $initialPos = $pos;

        if ( $pos >= strlen( $data ) )
        {
            return true;
        }
        $tagBeginPos = strpos( $data, '<', $pos );

        if ( $this->getOption( self::OPT_PARSE_LINE_BREAKS ) )
        {
            // Regard line break as a start tag position
            $lineBreakPos = strpos( $data, "\n", $pos );
            if ( $lineBreakPos !== false )
            {
                $tagBeginPos = $tagBeginPos === false ? $lineBreakPos : min( $tagBeginPos, $lineBreakPos );
            }
        }

        $tagName = '';
        $attributes = null;
        // If it doesn't begin with '<' then its a text node.
        if ( $tagBeginPos != $pos || $tagBeginPos === false )
        {
            $pos = $initialPos;
            $tagName = $newTagName = '#text';
            $noChildren = true;

            if ( !$tagBeginPos )
            {
                $tagBeginPos = strlen( $data );
            }

            $textContent = substr( $data, $pos, $tagBeginPos - $pos );

            $textContent = $this->washText( $textContent );

            $pos = $tagBeginPos;
            if ( $textContent === '' )
            {
                return false;
            }
        }
        // Process closing tag.
        elseif ( $data[$tagBeginPos] == '<' && $tagBeginPos + 1 < strlen( $data ) &&
                 $data[$tagBeginPos + 1] == '/' )
        {
            $tagEndPos = strpos( $data, '>', $tagBeginPos + 1 );
            if ( $tagEndPos === false )
            {
                $pos = $tagBeginPos + 1;

                $this->handleError( self::ERROR_SYNTAX, 'Wrong closing tag 1' );
                return false;
            }

            $pos = $tagEndPos + 1;
            $closedTagName = strtolower( trim( substr( $data, $tagBeginPos + 2, $tagEndPos - $tagBeginPos - 2 ) ) );

            // Find matching tag in ParentStack array
            $firstLoop = true;
            for ( $i = count( $this->ParentStack ) - 1; $i >= 0; $i-- )
            {
                $parentNames = $this->ParentStack[$i];
                if ( $parentNames[0] == $closedTagName )
                {
                    array_pop( $this->ParentStack );
                    if ( !$firstLoop )
                    {
                        $pos = $tagBeginPos;
                        return true;
                    }
                    // If newTagName was '' we don't break children loop
                    elseif ( $parentNames[1] !== '' )
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
                $firstLoop = false;
            }

            $this->handleError( self::ERROR_SYNTAX, "Wrong closing tag 2: &lt;/$closedTagName&gt;." );

            return false;
        }
        // Insert <br/> instead of linebreaks
        elseif ( $this->getOption( self::OPT_PARSE_LINE_BREAKS ) && $data[$tagBeginPos] == "\n" )
        {
            $newTagName = 'br';
            $noChildren = true;
            $pos = $tagBeginPos + 1;
        }
        //  Regular tag: get tag's name and attributes.
        else
        {
            $tagEndPos = strpos( $data, '>', $tagBeginPos );
            if ( $tagEndPos === false )
            {
                $pos = $tagBeginPos + 1;

                $this->handleError( self::ERROR_SYNTAX, 'Wrong opening tag' );
                return false;
            }

            $pos = $tagEndPos + 1;
            $tagString = substr( $data, $tagBeginPos + 1, $tagEndPos - $tagBeginPos - 1 );
            // Check for final backslash
            $noChildren = substr( $tagString, -1, 1 ) == '/' ? true : false;
            // Remove final backslash and spaces
            $tagString = preg_replace( "/\s*\/$/", "", $tagString );

            $firstSpacePos = strpos( $tagString, ' ' );
            if ( $firstSpacePos === false )
            {
                $tagName = strtolower( trim( $tagString ) );
                $attributeString = '';
            }
            else
            {
                $tagName = strtolower( substr( $tagString, 0, $firstSpacePos ) );
                $attributeString = substr( $tagString, $firstSpacePos + 1 );
                $attributeString = trim( $attributeString );
                // Parse attribute string
                if ( $attributeString )
                {
                    $attributes = $this->parseAttributes( $attributeString );
                }
            }

            // Determine tag's name
            if ( isset( $this->InputTags[$tagName] ) )
            {
                $thisInputTag = $this->InputTags[$tagName];

                if ( isset( $thisInputTag['name'] ) )
                {
                    $newTagName = $thisInputTag['name'];
                }
                else
                {
                    $newTagName = $this->callInputHandler( 'nameHandler', $tagName, $attributes );
                }
            }
            else
            {
                // @todo -cBase DO use XMLSchema
                $newTagName = $tagName;
                if ( $this->XMLSchema->exists( $tagName ) )
                {
                    $newTagName = $tagName;
                }
                else
                {
                    $this->handleError( self::ERROR_SYNTAX, "Unknown tag: &lt;$tagName&gt;." );
                    return false;
                }
            }

            // Check 'noChildren' property
            if ( isset( $thisInputTag['noChildren'] ) )
            {
                $noChildren = true;
            }

            $thisOutputTag = isset( $this->OutputTags[$newTagName] ) ? $this->OutputTags[$newTagName] : null;

            // Implementation of 'autoCloseOn' rule ( Handling of unclosed tags, ex.: <p>, <li> )
            if ( isset( $thisOutputTag['autoCloseOn'] ) &&
                 $parent &&
                 $parent->parentNode instanceof DOMElement &&
                 in_array( $parent->nodeName, $thisOutputTag['autoCloseOn'] ) )
            {
                // Wrong nesting: auto-close parent and try to re-parse this tag at higher level
                array_pop( $this->ParentStack );
                $pos = $tagBeginPos;
                return true;
            }

            // Append to parent stack
            if ( !$noChildren && $newTagName !== false )
            {
                $this->ParentStack[] = array( $tagName, $newTagName, $attributeString );
            }

            if ( !$newTagName )
            {
                // If $newTagName is an empty string then it's not a error
                if ( $newTagName === false )
                    $this->handleError( self::ERROR_SYNTAX, "Can't convert tag's name: &lt;$tagName&gt;." );

                return false;
            }

            // wordmatch.ini support
            if ( $attributeString )
            {
                $attributes = $this->wordMatchSupport( $newTagName, $attributes, $attributeString );
            }
        }

        // Create text or normal node.
        if ( $newTagName == '#text' )
        {
            $element = $this->Document->createTextNode( $textContent );
        }
        else
        {
            $element = $this->Document->createElement( $newTagName );
        }

        if ( $attributes )
        {
            $this->setAttributes( $element, $attributes );
        }

        // Append element as a child or set it as root if there is no parent.
        if ( $parent )
        {
            $parent->appendChild( $element );
        }
        else
        {
            $this->Document->appendChild( $element );
        }

        $params = array();
        $params[] =& $data;
        $params[] =& $pos;
        $params[] =& $tagBeginPos;
        $result = $this->callOutputHandler( 'parsingHandler', $element, $params );

        if ( $result === false )
        {
            // This tag is already parsed in handler
            if ( !$noChildren )
            {
                array_pop( $this->ParentStack );
            }
            return false;
        }

        if ( $this->QuitProcess )
        {
            return false;
        }

        // Process children
        if ( !$noChildren )
        {
            do
            {
                $parseResult = $this->parseTag( $data, $pos, $element );
                if ( $this->QuitProcess )
                {
                    return false;
                }
            }
            while ( $parseResult !== true );
        }

        return false;
    }

    /**
     * Helper functions for pass 1
     *
     * @return array
     */
    private function parseAttributes( $attributeString )
    {
        $attributes = array();
        // Valid characters for XML attributes
        // @see http://www.w3.org/TR/xml/#NT-Name
        $nameStartChar = ':A-Z_a-z\\xC0-\\xD6\\xD8-\\xF6\\xF8-\\x{2FF}\\x{370}-\\x{37D}\\x{37F}-\\x{1FFF}\\x{200C}-\\x{200D}\\x{2070}-\\x{218F}\\x{2C00}-\\x{2FEF}\\x{3001}-\\x{D7FF}\\x{F900}-\\x{FDCF}\\x{FDF0}-\\x{FFFD}\\x{10000}-\\x{EFFFF}';
        if (
            preg_match_all(
                "/\s+([$nameStartChar][$nameStartChar\-.0-9\\xB7\\x{0300}-\\x{036F}\\x{203F}-\\x{2040}]*)\s*=\s*(?:(?:\"([^\"]+?)\")|(?:'([^']+?)')|(?: *([^\"'\s]+)\s*))/u",
                " " . $attributeString,
                $attributeArray,
                PREG_SET_ORDER
            )
        ) {
            foreach ( $attributeArray as $attribute )
            {
                // Value will always be at the last position
                $value = trim( array_pop( $attribute ) );
                if ( !empty( $value ) )
                {
                    $attributes[strtolower( $attribute[1] )] = $value;
                }
            }
        }

        return $attributes;
    }

    /**
     * Sets attributes from $attributes on $element
     *
     * @param DOMElement $element
     * @param array $attributes
     */
    private function setAttributes( DOMElement $element, $attributes )
    {
        $thisOutputTag = $this->OutputTags[$element->nodeName];

        foreach ( $attributes as $key => $value )
        {
            // Convert attribute names
            if ( isset( $thisOutputTag['attributes'] ) &&
                 isset( $thisOutputTag['attributes'][$key] ) )
            {
                $qualifiedName = $thisOutputTag['attributes'][$key];
            }
            else
            {
                $qualifiedName = $key;
            }

            // Filter classes
            if ( $qualifiedName == 'class' )
            {
                $classesList = $this->XMLSchema->getClassesList( $element );
                if ( !in_array( $value, $classesList ) )
                {
                    $this->handleError( self::ERROR_DATA, "Class '$value' is not allowed for element &lt;$element->nodeName&gt; (check content.ini).");
                    continue;
                }
            }

            // Create attribute nodes
            if ( $qualifiedName )
            {
                if ( strpos( $qualifiedName, ':' ) )
                {
                    list( $prefix, $name ) = explode( ':', $qualifiedName );
                    if ( isset( $this->Namespaces[$prefix] ) )
                    {
                        $URI = $this->Namespaces[$prefix];
                        $element->setAttributeNS( $URI, $qualifiedName, $value );
                    }
                    else
                    {
                        // eZDebug::writeWarning( "No namespace defined for prefix '$prefix'.", 'eZXML input parser' );
                    }
                }
                else
                {
                    $element->setAttribute( $qualifiedName, $value );
                }
            }
        }

        // Check for required attrs are present
        if ( isset( $this->OutputTags[$element->nodeName]['requiredInputAttributes'] ) )
        {
            foreach ( $this->OutputTags[$element->nodeName]['requiredInputAttributes'] as $reqAttrName )
            {
                $presented = false;
                foreach ( $attributes as $key => $value )
                {
                    if ( $key == $reqAttrName )
                    {
                        $presented = true;
                        break;
                    }
                }
                if ( !$presented )
                {
                    $this->handleError( self::ERROR_SCHEMA, "Required attribute '$reqAttrName' is not presented in tag &lt;$element->nodeName&gt;." );
                }
            }
        }
    }

    protected function washText( $textContent )
    {
        $textContent = $this->entitiesDecode( $textContent );

        if ( !$this->getOption( self::OPT_ALLOW_NUMERIC_ENTITIES ) )
        {
            $textContent = $this->convertNumericEntities( $textContent );
        }

        if ( !$this->getOption( self::OPT_ALLOW_MULTIPLE_SPACES ) )
        {
            $textContent = preg_replace( "/ {2,}/", " ", $textContent );
        }

        return $textContent;
    }

    protected function entitiesDecode( $text )
    {
        $text = str_replace( '&#039;', "'", $text );

        $text = str_replace( '&gt;', '>', $text );
        $text = str_replace( '&lt;', '<', $text );
        $text = str_replace( '&apos;', "'", $text );
        $text = str_replace( '&quot;', '"', $text );
        $text = str_replace( '&amp;', '&', $text );
        return $text;
    }

    protected function convertNumericEntities( $text )
    {
        if ( strlen( $text ) < 4 )
        {
            return $text;
        }

        return $text;
        // Convert other HTML entities to the current charset characters.
        /*$codec = eZTextCodec::instance( 'unicode', false );
        $pos = 0;
        $domString = "";
        while ( $pos < strlen( $text ) - 1 )
        {
            $startPos = $pos;
            while ( !( $text[$pos] == '&' && $text[$pos + 1] == '#' ) && $pos < strlen( $text ) - 1 )
            {
                $pos++;
            }

            $domString .= substr( $text, $startPos, $pos - $startPos );

            if ( $pos < strlen( $text ) - 1 )
            {
                $endPos = strpos( $text, ';', $pos + 2 );
                if ( $endPos === false )
                {
                    $convertedText .= '&#';
                    $pos += 2;
                    continue;
                }

                $code = substr( $text, $pos + 2, $endPos - ( $pos + 2 ) );
                $char = $codec->convertString( array( $code ) );

                $pos = $endPos + 1;
                $domString .= $char;
            }
            else
            {
                $domString .= substr( $text, $pos, 2 );
            }
        }*/
        return $domString;
    }

    private function wordMatchSupport( $newTagName, $attributes, $attributeString )
    {
        $cfg = Configuration::getInstance( 'wordmatch' );
        if ( $cfg->has( $newTagName, 'MatchString' ) )
        {
            $matchArray = $cfg->get( $newTagName, 'MatchString' );
            if ( $matchArray )
            {
                foreach ( array_keys( $matchArray ) as $key )
                {
                    $matchString = $matchArray[$key];
                    if (  preg_match( "/$matchString/i", $attributeString ) )
                    {
                        $attributes['class'] = $key;
                        unset( $attributes['style'] );
                    }
                }
            }
        }
        return $attributes;
    }

    private function performPass2()
    {
        $tmp = null;

        $this->processSubtree( $this->Document->documentElement, $tmp );
    }

    /**
     * @param \DOMElement $element
     * @param $lastHandlerResult
     * @return mixed|null
     */
    private function processSubtree( DOMElement $element, &$lastHandlerResult )
    {
        $ret = null;
        $tmp = null;

        // Call "Init handler"
        $this->callOutputHandler( 'initHandler', $element, $tmp );

        $debug = false;

        // Process children
        if ( $element->hasChildNodes() )
        {
            // Make another copy of children to save primary structure
            $childNodes = $element->childNodes;
            $childrenCount = $childNodes->length;

            // we can not loop directly over the childNodes property, because this will change while we are working on it's parent's children
            $children = array();
            foreach ( $childNodes as $childNode )
            {
                $children[] = $childNode;
            }

            $lastResult = null;
            $newElements = array();
            foreach ( $children as $child )
            {
                if ( $debug )
                {
                    // eZDebug::writeDebug( 'processing children, current child: ' . $child->nodeName, eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext', __METHOD__ ) );
                }

                $childReturn = $this->processSubtree( $child, $lastResult );

                unset( $lastResult );
                if ( isset( $childReturn['result'] ) )
                {
                    if ( $debug )
                    {
                        // eZDebug::writeDebug( 'return result is set for child ' . $child->nodeName, eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext', __METHOD__ ) );
                    }

                    $lastResult = $childReturn['result'];
                }

                if ( isset( $childReturn['new_elements'] ) )
                {
                    $newElements = array_merge( $newElements, $childReturn['new_elements'] );
                }

                if ( $this->QuitProcess )
                {
                    return $ret;
                }
            }

            /*if ( $debug )
            {
                eZDebug::writeDebug( $this->Document->saveXML(),
                                     eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext',
                                                                  'XML before processNewElements for element ' . $element->nodeName ) );
            }*/

            // process elements created in children handlers
            $this->processNewElements( $newElements );

            /*if ( $debug )
            {
                eZDebug::writeDebug( $this->Document->saveXML(),
                                     eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext',
                                                                  'XML after processNewElements for element ' . $element->nodeName ) );
            }*/
        }

        // Call "Structure handler"
        if ( $debug )
        {
            /*eZDebug::writeDebug( $this->Document->saveXML(),
                                 eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext',
                                                              'XML before callOutputHandler structHandler for element ' . $element->nodeName ) );*/
        }

        $ret = $this->callOutputHandler( 'structHandler', $element, $lastHandlerResult );

        /*if ( $debug )
        {
            eZDebug::writeDebug( $this->Document->saveXML(),
                                 eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext',
                                                              'XML after callOutputHandler structHandler for element ' . $element->nodeName ) );
            eZDebug::writeDebug( $ret,
                                 eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext',
                                                              'return value of callOutputHandler structHandler for element ' . $element->nodeName ) );
        }*/

        // Process by schema (check if element is allowed to exist)
        if ( !$this->processBySchemaPresence( $element ) )
        {
            /*if ( $debug )
            {
                eZDebug::writeDebug( $this->Document->saveXML(),
                                     eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext',
                                                                  'XML after failed processBySchemaPresence for element ' . $element->nodeName ) );
            }*/
            return $ret;
        }

        /*if ( $debug )
        {
            eZDebug::writeDebug( $this->Document->saveXML(),
                                 eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext',
                                                              'XML after processBySchemaPresence for element ' . $element->nodeName ) );
        }*/

        // Process by schema (check place in the tree)
        if ( !$this->processBySchemaTree( $element ) )
        {
            /*if ( $debug )
            {
                eZDebug::writeDebug( $this->Document->saveXML(),
                                     eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext',
                                                                  'XML after failed processBySchemaTree for element ' . $element->nodeName ) );
            }*/
            return $ret;
        }

        /*if ( $debug )
        {
            eZDebug::writeDebug( $this->Document->saveXML(),
                                 eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext',
                                                              'XML after processBySchemaTree for element ' . $element->nodeName ) );
        }*/

        $tmp = null;
        // Call "Publish handler"
        $this->callOutputHandler( 'publishHandler', $element, $tmp );

        // Process attributes according to the schema
        if ( $element->hasAttributes() )
        {
            if ( !$this->XMLSchema->hasAttributes( $element ) )
            {
                self::removeAllAttributes( $element );
            }
            else
            {
                $this->processAttributesBySchema( $element );
            }
        }
        return $ret;
    }

    /**
     * Removes all attributes from $element
     *
     * @param DOMElement $element
     */
    private function removeAllAttributes( DOMElement $element )
    {
        $attribs = $element->attributes;
        for ( $i = $attribs->length - 1; $i >= 0; $i-- )
        {
            $element->removeAttributeNode( $attribs->item( $i ) );
        }
    }

    /**
     * Check if the element is allowed to exist in this document and remove it if not.
     *
     * @param DOMElement $element
     */
    private function processBySchemaPresence( DOMNode $element )
    {
        $parent = $element->parentNode;
        if ( $parent instanceof DOMElement )
        {
            // If this is a foreign element, remove it
            if ( !$this->XMLSchema->exists( $element ) )
            {
                if ( $element->nodeName == 'custom' )
                {
                    $this->handleError( self::ERROR_SCHEMA, "Custom tag '".$element->getAttribute( 'name' )."' is not allowed." );
                }
                $element = $parent->removeChild( $element );
                return false;
            }

            // Delete if children required and no children
            // If this is an auto-added element, then do not throw error

            if ( $element->nodeType == XML_ELEMENT_NODE && ( $this->XMLSchema->childrenRequired( $element ) || $element->getAttribute( 'children_required' ) )
                 && !$element->hasChildNodes() )
            {
                $element = $parent->removeChild( $element );
                if ( !$element->getAttributeNS( 'http://ez.no/namespaces/ezpublish3/temporary/', 'new-element' ) )
                {
                    $this->handleError( self::ERROR_SCHEMA, "&lt;$element->nodeName&gt; tag can't be empty." );
                    return false;
                }
            }
        }
        // TODO: break processing of any node that doesn't have parent
        //       and is not a root node.
        elseif ( $element->nodeName != 'section' )
        {
            return false;
        }
        return true;
    }

    /**
     * Check that $element has a correct position in the tree and fix it if not.
     *
     * @param DOMElement $element
     */
    protected function processBySchemaTree( $element )
    {
        $parent = $element->parentNode;

        if ( $parent instanceof DOMElement )
        {
            $schemaCheckResult = $this->XMLSchema->check( $parent, $element );
            if ( !$schemaCheckResult )
            {
                if ( $schemaCheckResult === false )
                {
                    // Remove indenting spaces
                    if ( $element->nodeType == XML_TEXT_NODE && !trim( $element->textContent ) )
                    {
                        $element = $parent->removeChild( $element );
                        return false;
                    }

                    $elementName = $element->nodeType == XML_ELEMENT_NODE ? '&lt;' . $element->nodeName . '&gt;' : $element->nodeName;
                    throw new \Exception( "'$elementName' is not allowed to be a child of &lt;$parent->nodeName&gt;." );
                    $this->handleError( self::ERROR_SCHEMA, "'$elementName' is not allowed to be a child of &lt;$parent->nodeName&gt;." );
                }
                $this->fixSubtree( $element, $element );
                return false;
            }
        }
        // TODO: break processing of any node that doesn't have parent
        //       and is not a root node.
        elseif ( $element->nodeName != 'section' )
        {
            return false;
        }
        return true;
    }

    /**
     * Removes nodes that don't match schema (recursively)
     *
     * @param \DOMElement $element
     * @param \DOMNode $mainChild
     */
    private function fixSubtree( DOMElement $element, DOMNode $mainChild )
    {
        $parent = $element->parentNode;
        $mainParent = $mainChild->parentNode;
        while ( $element->hasChildNodes() )
        {
            $child = $element->firstChild;

            $child = $element->removeChild( $child );
            $child = $mainParent->insertBefore( $child, $mainChild );

            if ( !$this->XMLSchema->check( $mainParent, $child ) )
            {
                $this->fixSubtree( $child, $mainChild );
            }
        }
        $parent->removeChild( $element );
    }

    /**
     * @param \DOMElement $element
     */
    protected function processAttributesBySchema( DOMElement $element )
    {
        // Remove attributes that don't match schema
        $schemaAttributes = $this->XMLSchema->attributes( $element );
        $schemaCustomAttributes = $this->XMLSchema->customAttributes( $element );

        $attributes = $element->attributes;

        for ( $i = $attributes->length - 1; $i >=0; $i-- )
        {
            $attr = $attributes->item( $i );
            if ( $attr->prefix == 'tmp' )
            {
                $element->removeAttributeNode( $attr );
                continue;
            }

            $allowed = false;
            $removeAttr = false;

            $fullName = $attr->prefix ? $attr->prefix . ':' . $attr->localName : $attr->nodeName;

            // check for allowed custom attributes (3.9)
            if ( $attr->prefix == 'custom' && in_array( $attr->localName, $schemaCustomAttributes ) )
            {
                $allowed = true;
            }
            else
            {
                if ( in_array( $fullName, $schemaAttributes ) )
                {
                   $allowed = true;
                }
                elseif ( in_array( $fullName, $schemaCustomAttributes ) )
                {
                    // add 'custom' prefix if it is not given
                    $allowed = true;
                    $removeAttr = true;
                    $element->setAttributeNS( $this->Namespaces['custom'], 'custom:' . $fullName, $attr->value );
                }
            }

            if ( !$allowed )
            {
                $removeAttr = true;
                $this->handleError( self::ERROR_SCHEMA, "Attribute '$fullName' is not allowed in &lt;$element->nodeName&gt; element.");
            }
            elseif ( $this->getOption( self::OPT_REMOVE_DEFAULT_ATTRS ) )
            {
                // Remove attributes having default values
                $default = $this->XMLSchema->attributeDefaultValue( $element, $fullName );
                if ( $attr->value === $default )
                {
                    $removeAttr = true;
                }
                else if ( ( $default === true || $default === false ) && $attr->value == $default )
                {
                    $removeAttr = true;
                }
            }

            if ( $removeAttr )
            {
                $element->removeAttributeNode( $attr );
            }
        }
    }

    /**
     * @param string $handlerName
     * @param string $tagName
     * @param array $attributes
     * @return mixed|null
     */
    protected function callInputHandler( $handlerName, $tagName, &$attributes )
    {
        $result = null;
        $thisInputTag = $this->InputTags[$tagName];
        if ( isset( $thisInputTag[$handlerName] ) )
        {
            if ( is_callable( array( $this, $thisInputTag[$handlerName] ) ) )
            {
                $result = call_user_func_array( array( $this, $thisInputTag[$handlerName] ),
                                                array( $tagName, &$attributes ) );
            }
            else
            {
                // @todo throw Exception
                throw new BadConfiguration( "inputHandler $handlerName ({$thisInputTag[$handlerName]}) for $tagName" );
            }
        }
        return $result;
    }

    /**
     * @param string $handlerName
     * @param \DOMNode $element
     * @param array $params
     * @return mixed|null
     * @throws \eZ\Publish\Core\Base\Exceptions\BadConfiguration
     */
    protected function callOutputHandler( $handlerName, DOMNode $element, &$params )
    {
        $result = null;
        $thisOutputTag = $this->OutputTags[$element->nodeName];
        if ( isset( $thisOutputTag[$handlerName] ) )
        {
            if ( is_callable( array( $this, $thisOutputTag[$handlerName] ) ) )
            {
                $result = call_user_func_array( array( $this, $thisOutputTag[$handlerName] ),
                                                array( $element, &$params ) );
            }
            else
            {
                throw new BadConfiguration( "outputHandler $handlerName ({$thisOutputTag[$handlerName]}) for $element->nodeName" );
            }
        }

        return $result;
    }

    /**
     * Creates new element from $elementName and adds it to array for further post-processing.
     *
     * Use this function if you need to process newly created element (check it by schema
     * and call 'structure' and 'publish' handlers)
     *
     * @param string $elementName
     * @param array $ret
     * @return \DOMElement the created element
     */
    protected function createAndPublishElement( $elementName, &$ret )
    {
        $element = $this->Document->createElement( $elementName );
        $element->setAttributeNS( 'http://ez.no/namespaces/ezpublish3/temporary/', 'tmp:new-element', 'true' );

        if ( !isset( $ret['new_elements'] ) )
        {
            $ret['new_elements'] = array();
        }

        $ret['new_elements'][] = $element;
        return $element;
    }

    /**
     * @param \DOMNode[] $createdElements
     */
    private function processNewElements( $createdElements )
    {
        // $debug = false;
        // eZDebugSetting::isConditionTrue( 'kernel-datatype-ezxmltext', eZDebug::LEVEL_DEBUG );
        // Call handlers for newly created elements
        foreach ( $createdElements as $element )
        {
            /*if ( $debug )
            {
                eZDebug::writeDebug( 'processing new element ' . $element->nodeName, eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext' ) );
            }*/

            $tmp = null;
            if ( !$this->processBySchemaPresence( $element ) )
            {
                /*if ( $debug )
                {
                    eZDebug::writeDebug( $this->Document->saveXML(),
                                         eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext',
                                                                      'xml string after failed processBySchemaPresence for new element ' . $element->nodeName ) );
                }*/
                continue;
            }

            /*if ( $debug )
            {
                eZDebug::writeDebug( $this->Document->saveXML(),
                                     eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext',
                                                                  'xml string after processBySchemaPresence for new element ' . $element->nodeName ) );
            }*/


            // Call "Structure handler"
            $this->callOutputHandler( 'structHandler', $element, $tmp );

            if ( !$this->processBySchemaTree( $element ) )
            {
                /*if ( $debug )
                {
                    eZDebug::writeDebug( $this->Document->saveXML(),
                                         eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext',
                                                                      'xml string after failed processBySchemaTree for new element ' . $element->nodeName ) );
                }*/
                continue;
            }

            /*if ( $debug )
            {
                eZDebug::writeDebug( $this->Document->saveXML(),
                                     eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext',
                                                                  'xml string after processBySchemaTree for new element ' . $element->nodeName ) );
            }*/


            $tmp2 = null;
            // Call "Publish handler"
            $this->callOutputHandler( 'publishHandler', $element, $tmp2 );

            /*if ( $debug )
            {
                eZDebug::writeDebug( $this->Document->saveXML(),
                                     eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext',
                                                                  'xml string after callOutputHandler publishHandler for new element ' . $element->nodeName ) );
            }*/

            // Process attributes according to the schema
            if ( $element->hasAttributes() )
            {
                if ( !$this->XMLSchema->hasAttributes( $element ) )
                {
                    self::removeAllAttributes( $element );
                }
                else
                {
                    $this->processAttributesBySchema( $element );
                }
            }
        }
    }

    /**
     * Returns the XML processing messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->Messages;
    }

    /**
     * Returns the validity status of the processed XML String
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->isInputValid;
    }

    /**
     * @param int $type
     * @param string $message
     */
    protected function handleError( $type, $message )
    {
        if ( $type & $this->getOption( self::OPT_DETECT_ERROR_LEVEL ) )
        {
            $this->IsInputValid = false;
            if ( $message )
            {
                $this->Messages[] = $message;
            }
        }

        if ( $type & $this->getOption( self::OPT_VALIDATE_ERROR_LEVEL ) )
        {
            $this->IsInputValid = false;
            $this->QuitProcess = true;
        }
    }

    /**
     * @return array|\integer[]
     */
    public function getRelatedContentIdArray()
    {
        return $this->relatedObjectIDArray;
    }

    /**
     * @return array|\integer[]
     */
    public function getLinkedContentIdArray()
    {
        return $this->linkedObjectIDArray;
    }

    /**
     * @return array|\integer[]
     */
    public function getUrlIdArray()
    {
        return $this->urlIDArray;
    }
}
