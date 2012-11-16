#!/usr/bin/env php
<?php
/**
 * File containing a simple fixture generator
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

if ( false === isset( $argv[1] ) || false === isset( $argv[2] ) )
{
    echo 'Usage: ', PHP_EOL,
         basename( __FILE__ ), ' <dump-file> <output-dir>', PHP_EOL;
    exit( 1 );
}

$fixture = include $argv[1];

writeFixtureFile( generateContentTypeGroupFixture( $fixture ), 'ContentTypeGroup', $argv[2] );
// echo generateContentTypeFixture( $fixture );
writeFixtureFile( generateContentTypeFixture( $fixture ), 'ContentType', $argv[2] );
writeFixtureFile( generateSectionFixture( $fixture ), 'Section', $argv[2] );
writeFixtureFile( generateLanguageFixture( $fixture ), 'Language', $argv[2] );
writeFixtureFile( generateUserFixture( $fixture ), 'User', $argv[2] );
writeFixtureFile( generateUserGroupFixture( $fixture ), 'UserGroup', $argv[2] );
writeFixtureFile( generateRoleFixture( $fixture ), 'Role', $argv[2] );
writeFixtureFile( generateContentInfoFixture( $fixture ), 'Content', $argv[2] );
writeFixtureFile( generateLocationFixture( $fixture ), 'Location', $argv[2] );
writeFixtureFile( generateURLAliasFixture( $fixture ), 'URLAlias', $argv[2] );
writeFixtureFile( generateObjectStateGroupFixture( $fixture ), 'ObjectStateGroup', $argv[2] );
writeFixtureFile( generateObjectStateFixture( $fixture ), 'ObjectState', $argv[2] );

function generateContentTypeGroupFixture( array $fixture )
{
    $nextId = 0;
    $groups = array();
    foreach ( getFixtureTable( 'ezcontentclassgroup', $fixture ) as $data )
    {
        $groups[$data['id']] = array(
            'id' => $data['id'],
            'identifier' => $data['name'],
            'creationDate' => dateCreateCall( $data['created'] ),
            'modificationDate' =>  dateCreateCall( $data['modified'] ),
            'creatorId' => $data['creator_id'],
            'modifierId' => $data['modifier_id']
        );
        $nextId = max( $nextId, $data['id'] );
    }

    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeGroupStub', $groups ),
        $nextId
    );
}

function generateContentTypeFixture( array $fixture )
{
    $languageCodes = array();
    foreach ( getFixtureTable( 'ezcontent_language', $fixture ) as $data )
    {
        $languageCodes[$data['id']] = $data['locale'];
    }

    $typeNames = array();
    foreach ( getFixtureTable( 'ezcontentclass_name', $fixture ) as $data )
    {
        if ( false === isset( $typeNames[$data['contentclass_id']] ) )
        {
            $typeNames[$data['contentclass_id']] = array();
        }
        $typeNames[$data['contentclass_id']][$data['language_locale']] = $data['name'];
    }

    $typeGroups = array();
    foreach ( getFixtureTable( 'ezcontentclass_classgroup', $fixture ) as $data )
    {
        if ( false === isset( $typeGroups[$data['contentclass_id']] ) )
        {
            $typeGroups[$data['contentclass_id']] = array();
        }
        $typeGroups[$data['contentclass_id']][] = '$scopeValues["groups"][' . valueToString( $data['group_id'] ) . ']';
    }

    list( $fieldDef, $nextFieldId ) = getContentTypeFieldDefinition( $fixture );

    $nextTypeId = 0;
    $types = array();
    foreach ( getFixtureTable( 'ezcontentclass', $fixture ) as $data )
    {
        $types[$data['id']] = array(
            'id' => $data['id'],
            'status' => 0, // Type::STATUS_DEFINED
            'identifier' => $data['identifier'],
            'creationDate' => dateCreateCall( $data['created'] ),
            'modificationDate' => dateCreateCall( $data['modified'] ),
            'creatorId' => $data['creator_id'],
            'modifierId' => $data['modifier_id'],
            'remoteId' => $data['remote_id'],
            // TODO: How do we build the userAliasSchema?
            //'urlAliasSchema' => $data[]
            'names' => $typeNames[$data['id']],
            'descriptions' => array(),
            'nameSchema' => $data['contentobject_name'],
            'isContainer' => (boolean) $data['is_container'],
            'mainLanguageCode' => $languageCodes[$data['initial_language_id']],
            'defaultAlwaysAvailable' => (boolean) $data['always_available'],
            'defaultSortField' => $data['sort_field'],
            'defaultSortOrder' => $data['sort_order'],

            'fieldDefinitions' => trim ( generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub', isset( $fieldDef[$data['id']] ) ? $fieldDef[$data['id']] : array() ) ),
            'contentTypeGroups' => isset( $typeGroups[$data['id']] ) ? $typeGroups[$data['id']] : array(),
        );

        $nextTypeId = max( $nextTypeId, $data['id'] );
    }

    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub', $types ),
        $nextTypeId,
        $nextFieldId
    );
}

function getContentTypeFieldDefinition( array $fixture )
{
    $nextFieldId = 0;
    $fieldDef = array();
    foreach ( getFixtureTable( 'ezcontentclass_attribute', $fixture ) as $data )
    {
        if ( false === isset( $fieldDef[$data['contentclass_id']] ) )
        {
            $fieldDef[$data['contentclass_id']] = array();
        }

        $names = filterTranslatedArray( unserialize( $data['serialized_name_list'] ) );

        $description = filterTranslatedArray( unserialize( $data['serialized_description_list'] ) );

        $fieldDef[$data['contentclass_id']][$data['id']] = array(
            'id' => (int) $data['id'],
            'identifier' => $data['identifier'],
            'fieldGroup' => $data['category'],
            'position' => (int) $data['placement'],
            'fieldTypeIdentifier' => $data['data_type_string'],
            'isTranslatable' => (boolean) $data['can_translate'],
            'isRequired' => (boolean) $data['is_required'],
            'isInfoCollector' => (boolean) $data['is_information_collector'],
            'isSearchable' => (boolean) $data['is_searchable'],
            'defaultValue' => null,

            'names' => $names,
            'descriptions' => $description,
            'fieldSettings' => array(),
            'validatorConfiguration' => array(),
        );

        $nextFieldId = max( $nextFieldId, $data['id'] );
    }

    return array( $fieldDef, $nextFieldId );
}

function filterTranslatedArray( array $translated )
{
    $filtered = array();
    foreach ( $translated as $languageCode => $translation )
    {
        if ( is_string( $languageCode ) )
        {
            $filtered[$languageCode] = $translation;
        }
    }
    unset( $filtered['always-available'] );
    return $filtered;
}

function generateSectionFixture( array $fixture )
{
    $nextId = 0;
    $sections = array();
    $identifiers = array();

    foreach ( getFixtureTable( 'ezsection', $fixture ) as $data )
    {
        $sections[$data['id']] = array(
            'id' => $data['id'],
            'name' => $data['name'],
            'identifier' => $data['identifier'],
        );

        if ( $data['identifier'] )
        {
            $identifiers[$data['identifier']] = $data['id'];
        }

        $nextId = max( $nextId, $data['id'] );
    }

    $assignedContents = array();

    foreach ( getFixtureTable( 'ezcontentobject', $fixture ) as $data )
    {
        $sectionId = (int) $data['section_id'];

        if ( !isset( $assignedContents[$sectionId] ) )
        {
            $assignedContents[$sectionId] = array();
        }
        $assignedContents[$sectionId][(int) $data['id']] = true;
    }

    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Values\Content\Section', $sections ),
        generateMapping( $identifiers ),
        generateMapping( $assignedContents ),
        $nextId
    );
}

function getLanguageCodes( array $fixture )
{
    $languageCodes = array();
    foreach ( getFixtureTable( 'ezcontent_language', $fixture ) as $data )
    {
        $languageCodes[$data['id']] = $data['locale'];
    }
    return $languageCodes;
}

function generateContentInfoFixture( array $fixture )
{
    $nextId = 0;
    $contentInfos = array();
    $indexMap = array();
    $languageCodes = getLanguageCodes( $fixture );

    $mainLocationIds = array();
    foreach ( getFixtureTable( 'ezcontentobject_tree', $fixture ) as $data )
    {
        $mainLocationIds[$data['contentobject_id']] = $data['main_node_id'];
    }

    foreach ( getFixtureTable( 'ezcontentobject', $fixture ) as $data )
    {
        $indexMap[$data['remote_id']] = array(
            'versionId' => array(),
            'contentId' => array(),
        );

        $indexMap[$data['id']] = array(
            'versionId' => array(),
            'contentId' => array(),
        );

        $contentInfos[$data['id']] = array(
            'id' => $data['id'],
            'name' => $data['name'],
            'contentTypeId' => $data['contentclass_id'],
            'sectionId' => $data['section_id'],
            'currentVersionNo' => $data['current_version'],
            'published' => ( $data['published'] != 0 ),
            'ownerId' => $data['owner_id'],
            'modificationDate' => dateCreateCall( $data['modified'] ),
            'publishedDate' => dateCreateCall( $data['published'] ),
            'alwaysAvailable' => (boolean) ( $data['language_mask'] & 1 ),
            'remoteId' => $data['remote_id'],
            'mainLanguageCode' => $languageCodes[$data['initial_language_id']],
            'repository' => '$this',
            'mainLocationId' => $mainLocationIds[$data['id']],
        );
        $nextId = max( $nextId, $data['id'] );
    }

    list( $fieldDef ) = getContentTypeFieldDefinition( $fixture );

    $fields = array();
    $fieldNextId = 0;

    foreach ( getFixtureTable( 'ezcontentobject_attribute', $fixture ) as $data )
    {
        if ( trim( $data['data_text'] ) )
        {
            $value = $data['data_text'];
        }
        else if ( is_numeric( $data['data_float'] ) && $data['data_float'] > 0 )
        {
            $value = $data['data_float'];
        }
        else
        {
            $value = $data['data_int'];
        }

        $identifier = null;
        foreach ( $fieldDef as $def )
        {
            if ( isset( $def[$data['contentclassattribute_id']] ) )
            {
                $identifier = $def[$data['contentclassattribute_id']]['identifier'];
                break;
            }
        }

        $fields[$data['id']] = array(
            'id' => $data['id'],
            'value' => $value,
            'languageCode' => $data['language_code'],
            'fieldDefIdentifier' => $identifier,

            'contentId' => $data['contentobject_id'],
            'version' => $data['version']
        );

        $fieldNextId = max( $fieldNextId, $data['id'] );
    }

    $names = array();
    foreach ( getFixtureTable( 'ezcontentobject_name', $fixture ) as $data )
    {
        $objectId = $data['contentobject_id'];
        $versionNo = $data['content_version'];

        if ( !isset( $names[$objectId] ) )
        {
            $names[$objectId] = array();
        }
        if ( !isset( $names[$objectId][$versionNo] ) )
        {
            $names[$objectId][$versionNo] = array();
        }

        foreach ( $languageCodes as $bit => $code )
        {
            if ( $data['language_id'] & $bit )
            {
                $names[$objectId][$versionNo][$code] = $data['name'];
            }
        }
    }

    $content = array();
    $versionInfo = array();
    $versionNextId = 0;
    foreach ( getFixtureTable( 'ezcontentobject_version', $fixture ) as $data )
    {
        $versionInfo[$data['id']] = array(
            'id' => $data['id'],
            'contentId' => $data['contentobject_id'],
            'status' => $data['status'] <= 2 ? $data['status'] : 1,
            'versionNo' => $data['version'],
            'modificationDate' => dateCreateCall( $data['modified'] ),
            'creatorId' => $data['creator_id'],
            'creationDate' => dateCreateCall( $data['created'] ),
            'initialLanguageCode' => $languageCodes[$data['initial_language_id']],
            'languageCodes' => array(), // TODO: Extract language codes from fields
            'repository' => '$this',
            'names' => $names[$data['contentobject_id']][$data['version']],
        );

        $versionNextId = max( $versionNextId, $data['id'] );

        $contentFields = array();
        foreach ( $fields as $field )
        {
            if ( $field['contentId'] != $data['contentobject_id'] )
            {
                continue;
            }
            if ( $field['version'] != $data['version'] )
            {
                continue;
            }

            unset( $field['contentId'], $field['version'] );

            $contentFields[] = $field;
        }

        $contentFields = trim( generateValueObjects( '\eZ\Publish\API\Repository\Values\Content\Field', $contentFields ) );

        $content[] = array(
            'id' => $data['contentobject_id'],
            'contentTypeId' => $contentInfos[$data['contentobject_id']]['contentTypeId'],
            'fields' => $contentFields,
            'relations' => array(),

            'versionNo' => $data['version'],
            'repository' => '$this'
        );

        $remoteId = $contentInfos[$data['contentobject_id']]['remoteId'];

        $versionId = $data['id'];
        $contentId = $data['contentobject_id'];

        $indexMap[$remoteId]['versionId'][$versionId] = $versionId;
        $indexMap[$remoteId]['contentId'][$contentId] = $contentId;
        $indexMap[$contentId]['versionId'][$versionId] = $versionId;
        $indexMap[$contentId]['contentId'][$contentId] = $contentId;
    }

    uasort( $versionInfo, function( $versionInfo1, $versionInfo2 ) {
        if ( $versionInfo1['contentId'] === $versionInfo2['contentId'] )
        {
            return $versionInfo2['versionNo'] - $versionInfo1['versionNo'];
        }
        return $versionInfo1['contentId'] - $versionInfo2['contentId'];
    } );

    uasort( $content, function( $content1, $content2 ) {
        if ( $content1['id'] === $content2['id'] )
        {
            return $content2['versionNo'] - $content1['versionNo'];
        }
        return $content1['id'] - $content2['id'];
    } );



    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\Content\ContentInfoStub', $contentInfos ),
        $nextId,
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\Content\VersionInfoStub', $versionInfo ),
        $versionNextId,
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\Content\ContentStub', $content ),
        generateMapping( $indexMap )
    );
}

function generateLocationFixture( array $fixture )
{
    $nextId = 0;
    $locations = array();

    foreach ( getFixtureTable( 'ezcontentobject_tree', $fixture ) as $data )
    {
        $locations[$data['node_id']] = array(
            'id' => $data['node_id'],
            'priority' => $data['priority'],
            'hidden' => (bool) $data['is_hidden'],
            'invisible' => (bool) $data['is_invisible'],
            'remoteId' => $data['remote_id'],
            'contentInfo' => ( $data['node_id'] == 1 ? null :createRepoCall(
                'ContentService',
                'loadContentInfo',
                array( $data['contentobject_id'] )
            ) ),
            'parentLocationId' => $data['parent_node_id'],
            'pathString' => $data['path_string'],
            'depth' => $data['depth'],
            'sortField' => $data['sort_field'],
            'sortOrder' => $data['sort_order'],
        );
        $nextId = max( $nextId, $data['node_id'] );
    }

    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub', $locations ),
        $nextId
    );
}

function createRepoCall( $serviceName, $methodName, array $params )
{
    foreach ( $params as $id => $param )
    {
        $params[$id] = valueToString( $param );
    }
    return sprintf(
        '$this->get%s()->%s( %s )',
        $serviceName,
        $methodName,
        implode( ', ', $params )
    );
}

function dateCreateCall( $timestamp )
{
    return sprintf(
        '$this->createDateTime( %s )',
        $timestamp
    );
}

function generateLanguageFixture( array $fixture )
{
    $nextId = 0;
    $languages = array();
    $languageCodes = array();

    foreach ( getFixtureTable( 'ezcontent_language', $fixture ) as $data )
    {
        $languages[$data['id']] = array(
            'id' => $data['id'],
            'name' => $data['name'],
            'enabled' => !$data['disabled'],
            'languageCode' => $data['locale']
        );

        $languageCodes[$data['locale']] = $data['id'];

        $nextId = max( $nextId, $data['id'] );
    }

    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Values\Content\Language', $languages ),
        generateMapping( $languageCodes ),
        $nextId
    );
}

function generateUserFixture( array $fixture )
{
    $users = array();
    foreach ( getFixtureTable( 'ezuser', $fixture ) as $data )
    {
        $users[] = array(
            '_id' => $data['contentobject_id'],
            'login' => $data['login'],
            'email' => $data['email'],
            'passwordHash' => $data['password_hash'],
            'hashAlgorithm' => $data['password_hash_type'],
            'enabled' => true,
            'content' => '$this->getContentService()->loadContent( ' . $data['contentobject_id'] . ' )'
        );
    }

    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserStub', $users )
    );
}

function generateUserGroupFixture( array $fixture )
{
    $classId = null;
    foreach ( getFixtureTable( 'ezcontentclass', $fixture ) as $data )
    {
        if ( 'user_group' === $data['identifier'] )
        {
            $classId = $data['id'];
            break;
        }
    }

    $node2parentId = array();
    $content2nodeId = array();
    foreach ( getFixtureTable( 'ezcontentobject_tree', $fixture ) as $data )
    {
        $content2nodeId[$data['contentobject_id']] = $data['node_id'];
        $node2parentId[$data['node_id']] = $data['parent_node_id'];
    }

    $groups = array();
    foreach ( getFixtureTable( 'ezcontentobject', $fixture ) as $data )
    {
        if ( $data['contentclass_id'] != $classId )
        {
            continue;
        }

        $parentId = null;
        if ( isset( $content2nodeId[$data['id']] ) )
        {
            $nodeId = $content2nodeId[$data['id']];
            if ( isset( $node2parentId[$nodeId] ) )
            {
                $parentId = array_search( $node2parentId[$nodeId], $content2nodeId );
            }
        }

        $groups[$data['id']] = array(
            '_id' => $data['id'],
            'parentId' => is_numeric( $parentId ) ? $parentId : 'null',
            'subGroupCount' => 0,
            'content' => '$this->getContentService()->loadContent( ' . $data['id'] . ' )'
        );
    }

    foreach ( $groups as $group )
    {
        if ( isset( $groups[$group['parentId']] ) )
        {
             ++$groups[$group['parentId']]['subGroupCount'];
        }
    }


    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub', $groups )
    );
}

function generateRoleFixture( array $fixture )
{
    $nextId = 0;
    $names = array();
    $roles = array();

    foreach ( getFixtureTable( 'ezrole', $fixture ) as $data )
    {
        $roles[$data['id']] = array(
            'id' => $data['id'],
            'identifier' => $data['name']
        );

        $names[$data['name']] = $data['id'];

        $nextId = max( $nextId, $data['id'] );
    }

    $content2role = array();
    $roleLimitations = array();

    foreach ( getFixtureTable( 'ezuser_role', $fixture ) as $data )
    {
        if ( false === isset( $content2role[$data['contentobject_id']] ) )
        {
            $content2role[$data['contentobject_id']] = array();
        }
        $content2role[$data['contentobject_id']][$data['id']] = $data['role_id'];

        if ( '' === trim( $data['limit_identifier'] ) )
        {
            continue;
        }

        $roleLimitations[$data['id']] = array(
            'id' => $data['id'],
            'roleId' => $data['role_id'],
            'contentId' => $data['contentobject_id'],
            'identifier' => $data['limit_identifier'],
            'value' => array( $data['limit_value'] )
        );
    }

    $role2policy = array();
    $policies = array();
    $policyNextId = 0;
    foreach ( getFixtureTable( 'ezpolicy', $fixture ) as $data )
    {
        if ( false === isset( $role2policy[$data['role_id']] ) )
        {
            $role2policy[$data['role_id']] = array();
        }
        $role2policy[$data['role_id']][] = $data['id'];

        $policies[$data['id']] = array(
            'id' => $data['id'],
            'roleId' => $data['role_id'],
            'module' => $data['module_name'],
            'function' => $data['function_name'],
            'limitations' => array()
        );

        $policyNextId = max( $policyNextId, $data['id'] );
    }

    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleStub', $roles ),
        generateMapping( $names ),
        $nextId,
        generateMapping( $content2role ),
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub', $policies ),
        $policyNextId,
        generateMapping( $role2policy ),
        generateMapping( $roleLimitations )
    );
}

function generateURLAliasFixture( array $fixture )
{
    $typeMap = array(
        'nop' => 2,
        'eznode' => 0,
        'module' => 1,
    );

    $languageCodes = getLanguageCodes( $fixture );

    $nextId = 0;
    $aliases = array();
    foreach ( getFixtureTable( 'ezurlalias_ml', $fixture ) as $data )
    {
        if ( $data['id'] == 46 || $data['id'] == 16 || $data['id'] == 206 )
        {
            // These 2 aliases are broken in the standard database, since they
            // don't have a valid location assigned
            continue;
        }

        $destination = null;
        switch ( $data['action_type'] )
        {
            case 'nop':
                $destination = null;
                break;

            case 'eznode':
                $destination = createRepoCall(
                    'LocationService',
                    'loadLocation',
                    array( substr( $data['action'], 7 ) )
                );
                break;

            case 'module':
                $destination = substr( $data['action'], 7 );
                break;
        }

        $aliases[$data['id']] = array(
            'id' => $data['id'],
            'type' => $typeMap[$data['action_type']],
            'destination' => $destination,
            'path' => getBaseUrlPath( $aliases, $data['parent'] ) . '/' . $data['text'],
            'languageCodes' => resolveLanguageMask( $languageCodes, $data['lang_mask'] ),
            'alwaysAvailable' => $data['lang_mask'] & 1,
            'isHistory' => !( $data['is_original'] ),
            'isCustom' => (bool)$data['is_alias'],
            'forward' => (bool)$data['alias_redirects'],
        );
        $nextId = max( $nextId, $data['id'] );
    }

    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Values\Content\URLAlias', $aliases ),
        $nextId
    );
}

function generateObjectStateGroupFixture( array $fixture )
{
    $languageCodes = getLanguageCodes( $fixture );

    $nextId = 0;
    $groups = array();
    foreach ( getFixtureTable( 'ezcobj_state_group', $fixture ) as $data )
    {
        $groups[$data['id']] = array(
            'id'                  => $data['id'],
            'identifier'          => $data['identifier'],
            'defaultLanguageCode' => $languageCodes[$data['default_language_id']],
            'names'               => array(),
            'descriptions'        => array(),
        );
        $nextId = max( $nextId, $data['id'] );
    }

    foreach ( getFixtureTable( 'ezcobj_state_group_language', $fixture ) as $data )
    {
        $groupId  = $data['contentobject_state_group_id'];
        // Set lowest bit to 0 (always_available)
        $langCode = $languageCodes[( $data['language_id'] & -2 )];

        $groups[$groupId]['names'][$langCode]        = $data['name'];
        $groups[$groupId]['descriptions'][$langCode] = $data['description'];
    }

    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\ObjectState\ObjectStateGroupStub', $groups ),
        $nextId
    );
}

function generateObjectStateFixture( array $fixture )
{
    $languageCodes = getLanguageCodes( $fixture );

    $nextId         = 0;
    $states         = array();
    $groupStateMap  = array();
    $objectStateMap = array();
    // For internal use only
    $stateGroupMap  = array();

    foreach ( getFixtureTable( 'ezcobj_state', $fixture ) as $data )
    {
        $states[$data['id']] = array(
            'id'                  => $data['id'],
            'identifier'          => $data['identifier'],
            'priority'            => (int) $data['priority'],
            'defaultLanguageCode' => $languageCodes[$data['default_language_id']],
            'languageCodes'       => resolveLanguageMask( $languageCodes, $data['language_mask'] ),
            'stateGroup'          => '$scopeValues["groups"][' . valueToString( $data['group_id'] ) . ']',
            'names'               => array(),
            'descriptions'        => array(),
        );
        $nextId = max( $nextId, $data['id'] );

        $groupId = $data['group_id'];
        if ( !isset( $groupStateMap[$groupId] ) )
        {
            $groupStateMap[$groupId] = array();
        }
        $groupStateMap[$groupId][$data['id']] = $data['id'];

        // For internal use only
        $stateGroupMap[$data['id']] = $groupId;
    }

    foreach ( getFixtureTable( 'ezcobj_state_language', $fixture ) as $data );
    {
        $stateId  = $data['contentobject_state_id'];
        // Set lowest bit to 0 (always_available)
        $langCode = $languageCodes[( $data['language_id'] & -2 )];

        $states[$stateId]['names'][$langCode]        = $data['name'];
        $states[$stateId]['descriptions'][$langCode] = $data['description'];
    }

    foreach ( getFixtureTable( 'ezcobj_state_link', $fixture ) as $data )
    {
        $stateId  = $data['contentobject_state_id'];
        $objectId = $data['contentobject_id'];

        $groupId = $stateGroupMap[$stateId];

        if ( !isset( $objectStateMap[$objectId] ) )
        {
            $objectStateMap[$objectId] = array();
        }
        $objectStateMap[$objectId][$groupId] = $stateId;
    }

    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\ObjectState\ObjectStateStub', $states ),
        var_export( $groupStateMap , true ),
        var_export( $objectStateMap, true ),
        $nextId
    );
}

function resolveLanguageMask( array $languageCodes, $mask )
{
    $assignedCodes = array();
    foreach ( $languageCodes as $id => $languageCode )
    {
        if ( $mask & $id )
        {
            $assignedCodes[] = $languageCode;
        }
    }
    return $assignedCodes;
}

function getBaseUrlPath( array $aliases, $parentId )
{
    if ( $parentId == 0 )
    {
        return '';
    }
    if ( !isset( $aliases[$parentId] ) )
    {
        throw new RuntimeException( sprintf( 'Alias with ID %s not found.', $parentId ) );
    }
    return $aliases[$parentId]['path'];
}

function getUser2GroupMapping( array $fixture )
{
    $users = array();
    foreach ( getFixtureTable( 'ezuser', $fixture ) as $data )
    {
        $users[] = (int) $data['contentobject_id'];
    }

    $nodes = array();
    $contents = array();
    foreach ( getFixtureTable( 'ezcontentobject_tree', $fixture ) as $data )
    {
        $nodes[$data['node_id']] = $data['parent_node_id'];
        $contents[$data['node_id']] = $data['contentobject_id'];
    }

    $groups = array();
    foreach ( $contents as $key => $contentId )
    {
        if ( false === in_array( $contentId, $users ) )
        {
            continue;
        }

        if ( false === isset( $groups[$contentId] ) )
        {
            $groups[$contentId] = array();
        }
        $groups[$contentId][] = $contents[$nodes[$key]];
    }

    return $groups;
}

function generateReturnArray()
{
    return '<?php' . PHP_EOL .
        'return array(' . PHP_EOL .
        join( ',' . PHP_EOL . '    ', func_get_args() ) . PHP_EOL .
        ');' . PHP_EOL;
}

function generateMapping( array $mapping )
{
    $code = 'array(' . PHP_EOL;
    foreach ( $mapping as $key => $value )
    {
        $code .= '        "' . $key . '" => ' . valueToString( $value ) . ',' . PHP_EOL;
    }
    $code .= '    )';

    return $code;
}

function generateValueObjects( $class, array $objects )
{
    $code = '    array(' . PHP_EOL;
    foreach ( $objects as $object )
    {
        $code .= generateValueObject( $class, $object );
    }
    $code .= '    )';

    return $code;
}

function generateValueObject( $class, array $object )
{
    $id = isset( $object['id'] )
        ? $object['id']
        : $object['_id'];
    $code = '        ' . $id . ' => new ' . $class . '(' . PHP_EOL .
            '            array(' . PHP_EOL;
    foreach ( $object as $name => $value )
    {
        if ( $name[0] == '_' )
        {
            continue;
        }

        $code .= '                "' . $name . '" => ' . valueToString( $value ) . ',' . PHP_EOL;
    }

    $code .= '            )' . PHP_EOL .
             '        ),' . PHP_EOL;

    return $code;
}

function valueToString( $value, $indent = 4 )
{
    if ( is_numeric( $value ) )
    {
        $value = $value;
    }
    else if ( is_bool( $value ) )
    {
        $value = $value ? 'true' : 'false';
    }
    else if ( is_null( $value ) )
    {
        $value = 'null';
    }
    else if ( is_string( $value ) && 0 !== strpos( $value, 'new \\' ) && 0 !== strpos( $value, '$scopeValues[' ) && 0 !== strpos( $value, 'array(' ) && 0 !== strpos( $value, '$this' ) )
    {
        $value = '"' . str_replace( '"', '\"', $value ) . '"';
    }
    else if ( is_array( $value ) )
    {
        $code = 'array(' . PHP_EOL;
        foreach ( $value as $key => $val )
        {
            $code .= str_repeat( '    ', $indent + 1 ) .
                     valueToString( $key ) .
                     ' => ' .
                     valueToString( $val, $indent + 1 ) .
                     ',' . PHP_EOL;
        }
        $value = $code . str_repeat( '    ', $indent ) . ')';
    }

    return $value;
}

function getFixtureTable( $tableName, array $fixture )
{
    if ( isset( $fixture[$tableName] ) )
    {
        return $fixture[$tableName];
    }
    return array();
}

function writeFixtureFile( $code, $file, $dir )
{
    file_put_contents( "{$dir}/{$file}Fixture.php", $code );
}
