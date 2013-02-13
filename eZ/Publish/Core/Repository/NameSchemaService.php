<?php
/**
 * File containing the NameSchemaService class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;

/**
 * NameSchemaService is internal service for resolving content name and url alias patterns.
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
 *
 * @internal
 */
class NameSchemaService
{
    /**
     * The string to use to signify group tokens.
     *
     * @var string
     */
    const META_STRING = 'EZMETAGROUP_';

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Constructs a object to resolve $nameSchema with $contentVersion fields values
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param array $settings
     *
     * @return \eZ\Publish\Core\Repository\NameSchemaService
     */
    public function __construct( RepositoryInterface $repository, array $settings = array() )
    {
        $this->repository = $repository;
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + array(
            'limit' => 150,
            'sequence' => '...',
        );
    }

    /**
     * Convenience method for resolving URL alias schema
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType|null $contentType
     *
     * @return array
     */
    public function resolveUrlAliasSchema( Content $content, ContentType $contentType = null )
    {
        if ( $contentType === null )
        {
            $contentType = $this->repository->getContentTypeService()->loadContentType(
                $content->contentInfo->contentTypeId
            );
        }

        return $this->resolve(
            strlen( $contentType->urlAliasSchema ) === 0 ?
                $contentType->nameSchema :
                $contentType->urlAliasSchema,
            $contentType,
            $content->fields,
            $content->versionInfo->languageCodes
        );
    }

    /**
     * Convenience method for resolving name schema
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param array $fieldMap
     * @param array $languageCodes
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType|null $contentType
     *
     * @return array
     */
    public function resolveNameSchema( Content $content, array $fieldMap = array(), array $languageCodes = array(), ContentType $contentType = null )
    {
        if ( $contentType === null )
        {
            $contentType = $this->repository->getContentTypeService()->loadContentType(
                $content->contentInfo->contentTypeId
            );
        }

        $languageCodes = $languageCodes ?: $content->versionInfo->languageCodes;
        return $this->resolve(
            $contentType->nameSchema,
            $contentType,
            $this->mergeFieldMap(
                $content,
                $fieldMap,
                $languageCodes
            ),
            $languageCodes
        );
    }

    /**
     * Convenience method for resolving name schema
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param array $fieldMap
     * @param array $languageCodes
     *
     * @return array
     */
    protected function mergeFieldMap( Content $content, array $fieldMap, array $languageCodes )
    {
        if ( empty( $fieldMap ) )
        {
            return $content->fields;
        }

        $mergedFieldMap = array();

        foreach ( $content->fields as $fieldIdentifier => $fieldLanguageMap )
        {
            foreach ( $languageCodes as $languageCode )
            {
                $mergedFieldMap[$fieldIdentifier][$languageCode] = isset( $fieldMap[$fieldIdentifier][$languageCode] )
                    ? $fieldMap[$fieldIdentifier][$languageCode]
                    : $fieldLanguageMap[$languageCode];
            }
        }

        return $mergedFieldMap;
    }

    /**
     * Returns the real name for a content name pattern
     *
     * @param string $nameSchema
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param array $fieldMap
     * @param array $languageCodes
     *
     * @return string
     */
    public function resolve( $nameSchema, ContentType $contentType, array $fieldMap, array $languageCodes )
    {
        list( $filteredNameSchema, $groupLookupTable ) = $this->filterNameSchema( $nameSchema );
        $tokens = $this->extractTokens( $filteredNameSchema );
        $schemaIdentifiers = $this->getIdentifiers( $nameSchema );

        $names = array();

        foreach ( $languageCodes as $languageCode )
        {
            // Fetch titles for language code
            $titles = $this->getFieldTitles( $schemaIdentifiers, $contentType, $fieldMap, $languageCode );
            $name = $filteredNameSchema;

            // Replace tokens with real values
            foreach ( $tokens as $token )
            {
                $string = $this->resolveToken( $token, $titles, $groupLookupTable );
                $name = str_replace( $token, $string, $name );
            }

            // Make sure length is not longer then $limit unless it's 0
            if ( $this->settings["limit"] && strlen( $name ) > $this->settings["limit"] )
            {
                $name = rtrim( substr( $name, 0, $this->settings["limit"] - strlen( $this->settings["sequence"] ) ) ) . $this->settings["sequence"];
            }

            $names[$languageCode] = $name;
        }

        return $names;
    }

    /**
     * Fetches the list of available Field identifiers in the token and returns
     * an array of their current title value.
     *
     * @see \eZ\Publish\Core\Repository\FieldType::getName()
     *
     * @param string[] $schemaIdentifiers
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType $fieldDefinitions
     * @param array $fieldMap
     * @param string $languageCode
     *
     * @return string[] Key is the field identifier, value is the title value
     */
    protected function getFieldTitles( array $schemaIdentifiers, ContentType $contentType, array $fieldMap, $languageCode )
    {
        $fieldTitles = array();

        foreach ( $schemaIdentifiers as $fieldDefinitionIdentifier )
        {
            if ( isset( $fieldMap[$fieldDefinitionIdentifier][$languageCode] ) )
            {
                $fieldDefinition = $contentType->getFieldDefinition( $fieldDefinitionIdentifier );
                $fieldType = $this->repository->getFieldTypeService()->getFieldType(
                    $fieldDefinition->fieldTypeIdentifier
                );
                $fieldTitles[$fieldDefinitionIdentifier] = $fieldType->getName(
                    $fieldMap[$fieldDefinitionIdentifier][$languageCode]
                );
            }
        }

        return $fieldTitles;
    }

    /**
     * Extract all tokens from $namePattern
     *
     * Example:
     * <code>
     * Text &lt;token&gt; more text ==&gt; &lt;token&gt;
     * </code>
     *
     * @param string $nameSchema
     *
     * @return array
     */
    protected function extractTokens( $nameSchema )
    {
        preg_match_all(
            "|<([^>]+)>|U",
            $nameSchema,
            $tokenArray
        );

        return $tokenArray[0];
    }

    /**
     * Looks up the value $token should be replaced with and returns this as
     * a string. Meta strings denoting token groups are automatically
     * inferred.
     *
     * @param string $token
     * @param array $titles
     *
     * @param array $groupLookupTable
     *
     * @return string
     */
    protected function resolveToken( $token, $titles, $groupLookupTable )
    {
        $replaceString = "";
        $tokenParts = $this->tokenParts( $token );

        foreach ( $tokenParts as $tokenPart )
        {
            if ( $this->isTokenGroup( $tokenPart ) )
            {
                $replaceString = $groupLookupTable[$tokenPart];
                $groupTokenArray = $this->extractTokens( $replaceString );

                foreach ( $groupTokenArray as $groupToken )
                {
                    $replaceString = str_replace(
                        $groupToken,
                        $this->resolveToken(
                            $groupToken,
                            $titles,
                            $groupLookupTable
                        ),
                        $replaceString
                    );
                }

                // We want to stop after the first matching token part / identifier is found
                // <id1|id2> if id1 has a value, id2 will not be used.
                // In this case id1 or id1 is a token group.
                break;
            }
            else
            {
                if ( array_key_exists( $tokenPart, $titles ) && $titles[$tokenPart] !== '' && $titles[$tokenPart] !== null )
                {
                    $replaceString = $titles[$tokenPart];
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
     *
     * @return boolean
     */
    protected function isTokenGroup( $identifier )
    {
        if ( strpos( $identifier, self::META_STRING ) === false )
        {
            return false;
        }

        return true;
    }

    /**
     * Returns the different constituents of $token in an array.
     * The normal case here is that the different identifiers within one token
     * will be tokenized and returned.
     *
     * Example:
     * <code>
     * "&lt;title|text&gt;" ==&gt; array( 'title', 'text' )
     * </code>
     *
     * @param string $token
     *
     * @return array
     */
    protected function tokenParts( $token )
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
     * @param string $nameSchema
     *
     * @return string
     */
    protected function filterNameSchema( $nameSchema )
    {
        $retNamePattern = "";
        $foundGroups = preg_match_all( "/[<|\\|](\\(.+\\))[\\||>]/U", $nameSchema, $groupArray );
        $groupLookupTable = array();

        if ( $foundGroups )
        {
            $i = 0;
            foreach ( $groupArray[1] as $group )
            {
                // Create meta-token for group
                $metaToken = self::META_STRING . $i;

                // Insert the group with its placeholder token
                $retNamePattern = str_replace( $group, $metaToken, $nameSchema );

                // Remove the pattern "(" ")" from the tokens
                $group = str_replace( array( '(', ')' ), '', $group );

                $groupLookupTable[$metaToken] = $group;
                ++$i;
            }
            $nameSchema = $retNamePattern;
        }

        return array( $nameSchema, $groupLookupTable );
    }

    /**
     * Returns all identifiers from all tokens in the name schema.
     *
     * @param string $schemaString
     *
     * @return array
     */
    protected function getIdentifiers( $schemaString )
    {
        $allTokens = '#<(.*)>#U';
        $identifiers = '#\\W#';

        $tmpArray = array();
        preg_match_all( $allTokens, $schemaString, $matches );

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
