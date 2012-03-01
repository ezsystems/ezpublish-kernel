<?php
/**
 * File containing the NamePatternResolver class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Utils;
use ezp\Content\Version;

/**
 * NamePatternResolver is a utility class for resolving content name and url alias patterns.
 * This code supports content name pattern groups.
 *
 * Syntax:
 * <code>
 * &lt;attribute_identifier&gt;
 * &lt;attribute_identifier&gt; &lt;2nd-identifier&gt;
 * User text &lt;attribute_identifier&gt;|(&lt;2nd-identifier&gt;&lt;3rd-identifier&gt;)
 * </code>
 *
 * Example:
 * <code>
 * &lt;nickname|(&lt;firstname&gt; &lt;lastname&gt;)&gt;
 * </code>
 *
 * Tokens are looked up from left to right. If a match is found for the
 * leftmost token, the 2nd token will not be used. Tokens are representations
 * of fields. So a match means that that the current field has data.
 *
 * Tokens are the field definition identifiers which are used in the class edit-interface.
 */
class NamePatternResolver
{
    /**
     * The string to use to signify group tokens.
     *
     * @var string
     */
    const META_STRING = 'EZMETAGROUP_';

    /**
     * Max length of content name.
     *
     * @var int
     */
    const CONTENT_NAME_MAX_LENGTH = 255;

    /**
     * Holds token groups
     *
     * @var array
     */
    private $groupLookupTable;

    /**
     * Contains the original name pattern entered
     *
     * @var string
     */
    private $origNamePattern;

    /**
     * Holds the filtered name pattern where token groups are replaced with
     * meta strings
     *
     * @var string
     */
    private $namePattern;

    /**
     * The content object which holds the attributes used to resolve name pattern.
     *
     * @var \ezp\Content\Version
     */
    private $contentVersion;

    /**
     * Holds data fetched from content fields' Value object.
     * Key is the field identifier.
     * Value is the string value obtained via {@link \ezp\Content\FieldType\ValueInterface::getTitle()}
     *
     * @see \ezp\Content\FieldType\ValueInterface::getTitle()
     * @var string[]
     */
    private $fieldTitleArray;

    /**
     * Constructs a object to resolve $namePattern with $contentVersion fields values
     *
     * @param string $namePattern Name pattern (aka "name schema" for content name/urlAlias.
     *                            See {@link \eZ\Publish\SPI\Persistence\Content\Type::$nameSchema} for more info.
     * @param \ezp\Content\Version $contentVersion
     * @todo Take translation into account
     */
    public function __construct( $namePattern, Version $contentVersion )
    {
        $this->origNamePattern = $namePattern;
        $this->contentVersion = $contentVersion;
        $this->namePattern = $this->filterNamePattern( $namePattern );
    }

    /**
     * Return the real name for a content name pattern
     *
     * @param int $limit The limit on the string length, by defaul 0 aka none
     * @param string $sequence End sequence applied to string if limit has been reached
     * @return string
     */
    public function resolveNamePattern( $limit = 0, $sequence = '' )
    {
        // Fetch fields title for present identifiers
        $this->fieldTitleArray = $this->getFieldsTitle( $this->origNamePattern );

        // Replace tokens with real values
        $objectName = $this->translatePattern();

        // Make sure length is not longer then $limit unless it's 0
        if ( !$limit || strlen( $objectName ) <= $limit )
        {
            return $objectName;
        }
        else
        {
            return rtrim( substr( $objectName, 0, $limit - strlen( $sequence ) + 1 ) ) . $sequence;
        }
    }

    /**
     * Fetches the list of available Field identifiers in the token and returns
     * an array of their current title value.
     *
     * @see \ezp\Content\FieldType\ValueInterface::getTitle()
     * @return string[] Key is the field identifier, value is the title value
     */
    private function getFieldsTitle( $namePattern )
    {
        $returnFieldTitleArray = array();
        $fields = $this->contentVersion->getFields();

        foreach ( $this->getIdentifiers( $namePattern ) as $fieldIdentifier )
        {
            if ( isset( $fields[$fieldIdentifier] ) )
            {
                $returnFieldTitleArray[$fieldIdentifier] = $fields[$fieldIdentifier]->getValue()->getTitle();
            }
            // @todo : Shouldn't we log an error here if $fieldIdentifier is invalid ?
        }

        return $returnFieldTitleArray;
    }

    /**
     * Replaces tokens in the name pattern with their resolved values.
     *
     * @return string
     */
    private function translatePattern()
    {
        $tokenArray = $this->extractTokens( $this->namePattern );
        $objectName = $this->namePattern;

        foreach ( $tokenArray as $token )
        {
            $string = $this->resolveToken( $token );
            $objectName = str_replace( $token, $string, $objectName );
        }

        return $objectName;
    }

    /**
     * Extract all tokens from $namePattern
     *
     * Example:
     * <code>
     * Text &lt;token&gt; more text ==&gt; &lt;token&gt;
     * </code>
     *
     * @param string $namePattern
     * @return array
     */
    private function extractTokens( $namePattern )
    {
        $foundTokens = preg_match_all( "|<([^>]+)>|U", $namePattern,
                                                       $tokenArray );

        return $tokenArray[0];
    }

    /**
     * Looks up the value $token should be replaced with and returns this as
     * a string. Meta strings denothing token groups are automatically
     * inferred.
     *
     * @param string $token
     * @return string
     */
    private function resolveToken( $token )
    {
        $replaceString = "";
        $tokenParts = $this->tokenParts( $token );

        foreach ( $tokenParts as $tokenPart )
        {
            if ( $this->isTokenGroup( $tokenPart ) )
            {
                $groupTokenArray = $this->extractTokens( $this->groupLookupTable[$tokenPart] );
                $replaceString = $this->groupLookupTable[$tokenPart];

                foreach ( $groupTokenArray as $groupToken )
                {
                    $replaceString = str_replace( $groupToken, $this->resolveToken( $groupToken ), $replaceString );
                }
                // We want to stop after the first matching token part / identifier is found
                // <id1|id2> if id1 has a value, id2 will not be used.
                // In this case id1 or id1 is a token group.
                break;
            }
            else
            {
                if ( array_key_exists( $tokenPart, $this->fieldTitleArray ) and $this->fieldTitleArray[$tokenPart] !== '' and $this->fieldTitleArray[$tokenPart] !== NULL )
                {
                    $replaceString = $this->fieldTitleArray[$tokenPart];
                    // We want to stop after the first matching token part / identifier is found
                    // <id1|id2> if id1 has a value, id2 will not be used.
                    break;
                }
            }
        }

        return $replaceString;
    }

    /**
     * Checks whether $identifier is a placeholder for a token group.
     *
     * @param string $identifier
     * @return void
     */
    private function isTokenGroup( $identifier )
    {
        if ( strpos( $identifier, self::META_STRING ) === false )
        {
            return false;
        }

        return true;
    }

    /**
     * Return the different constituents of $token in an array.
     * The normal case here is that the different identifiers within one token
     * will be tokenized and returned.
     *
     * Example:
     * <code>
     * "&lt;title|text&gt;" ==&gt; array( 'title', 'text' )
     * </code>
     *
     * @param string $token
     * @return array
     */
    private function tokenParts( $token )
    {
        return preg_split( '#\\W#', $token, -1, PREG_SPLIT_NO_EMPTY );
    }

    /**
     * Builds a lookup / translation table for groups in the $namePattern.
     * The groups are referenced with a generated meta-token in the original
     * name pattern.
     *
     * Returns intermediate name pattern where groups are replaced with meta-
     * tokens.
     *
     * @param string $namePattern
     * @return string
     */
    private function filterNamePattern( $namePattern )
    {
        $retNamePattern = "";
        $foundGroups = preg_match_all( "/[<|\\|](\\(.+\\))[\\||>]/U", $namePattern, $groupArray );

        if ( $foundGroups )
        {
            $i = 0;
            foreach ( $groupArray[1] as $group )
            {
                // Create meta-token for group
                $metaToken = self::META_STRING . $i;

                // Insert the group with its placeholder token
                $retNamePattern = str_replace( $group, $metaToken, $namePattern );

                // Remove the pattern "(" ")" from the tokens
                $group = str_replace( array( '(', ')' ), '', $group );

                $this->groupLookupTable[$metaToken] = $group;
                ++$i;
            }
            return $retNamePattern;
        }

        return $namePattern;
    }

    /**
     * Returns all identifiers from all tokens in the name pattern.
     *
     * @param string $patternString
     * @return array
     */
    private function getIdentifiers( $patternString )
    {
        $allTokens = '#<(.*)>#U';
        $identifiers = '#\\W#';

        $tmpArray = array();
        preg_match_all( $allTokens, $patternString, $matches );

        foreach ( $matches[1] as $match )
        {
            $tmpArray[] = preg_split( $identifiers, $match, -1, PREG_SPLIT_NO_EMPTY );
        }

        $retArray = array();
        foreach ( $tmpArray as $matchGroup )
        {
            if ( is_array( $matchGroup ) )
            {
                foreach ( $matchGroup as $item )
                {
                    $retArray[] = $item;
                }
            }
            else
            {
                $retArray[] = $matchGroup;
            }
        }

        return $retArray;
    }
}
