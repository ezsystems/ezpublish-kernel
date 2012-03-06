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

function generateContentTypeGroupFixture( array $fixture )
{
    $nextId = 0;
    $groups = array();
    foreach ( getFixtureTable( 'ezcontentclassgroup', $fixture ) as $data )
    {
        $groups[$data['id']] = array(
            'id'                =>  $data['id'],
            'identifier'        =>  $data['name'],
            'creationDate'      =>  'new \DateTime( "@' . $data['created'] . '" )',
            'modificationDate'  =>  'new \DateTime( "@' . $data['modified'] . '" )',
            'creatorId'         =>  $data['creator_id'],
            'modifierId'        =>  $data['modifier_id']
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
    $types      = array();
    foreach ( getFixtureTable( 'ezcontentclass', $fixture ) as $data )
    {
        $types[$data['id']] = array(
            'id'                      =>  $data['id'],
            'status'                  =>  0, // Type::STATUS_DEFINED
            'identifier'              =>  $data['identifier'],
            'creationDate'            =>  'new \DateTime( "@' . $data['created'] . '" )',
            'modificationDate'        =>  'new \DateTime( "@' . $data['modified'] . '" )',
            'creatorId'               =>  $data['creator_id'],
            'modifierId'              =>  $data['modifier_id'],
            'remoteId'                =>  $data['remote_id'],
            // TODO: How do we build the userAliasSchema?
            //'urlAliasSchema'          =>  $data[]
            'names'                   =>  $typeNames[$data['id']],
            'descriptions'            =>  array(),
            'nameSchema'              =>  $data['contentobject_name'],
            'isContainer'             =>  (boolean) $data['is_container'],
            'mainLanguageCode'        =>  $languageCodes[$data['initial_language_id']],
            'defaultAlwaysAvailable'  =>  (boolean) $data['always_available'],
            'defaultSortField'        =>  $data['sort_field'],
            'defaultSortOrder'        =>  $data['sort_order'],

            'fieldDefinitions'        =>  trim ( generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub', isset( $fieldDef[$data['id']] ) ? $fieldDef[$data['id']] : array() ) ),
            'contentTypeGroups'       =>  isset( $typeGroups[$data['id']] ) ? $typeGroups[$data['id']] : array(),
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
    $fieldDef    = array();
    foreach ( getFixtureTable( 'ezcontentclass_attribute', $fixture ) as $data )
    {
        if ( false === isset( $fieldDef[$data['contentclass_id']] ) )
        {
            $fieldDef[$data['contentclass_id']] = array();
        }

        $names = unserialize( $data['serialized_name_list'] );
        unset( $names['always-available'] );

        $description = unserialize( $data['serialized_description_list'] );
        unset( $description['always-available'] );

        $fieldDef[$data['contentclass_id']][$data['id']] = array(
            'id'                   =>  (int) $data['id'],
            'identifier'           =>  $data['identifier'],
            'fieldGroup'           =>  $data['category'],
            'position'             =>  (int) $data['placement'],
            'fieldTypeIdentifier'  =>  $data['data_type_string'],
            'isTranslatable'       =>  (boolean) $data['can_translate'],
            'isRequired'           =>  (boolean) $data['is_required'],
            'isInfoCollector'      =>  (boolean) $data['is_information_collector'],
            'isSearchable'         =>  (boolean) $data['is_searchable'],
            'defaultValue'         =>  null,

            'names'                =>  $names,
            'descriptions'         =>  $description,
            'fieldSettings'        =>  array(),
            'validators'           =>  array(),
        );

        $nextFieldId = max( $nextFieldId, $data['id'] );
    }

    return array( $fieldDef, $nextFieldId );
}

function generateSectionFixture( array $fixture )
{
    $nextId      = 0;
    $sections    = array();
    $identifiers = array();

    foreach ( getFixtureTable( 'ezsection', $fixture ) as $data )
    {
        $sections[$data['id']] = array(
            'id'          =>  $data['id'],
            'name'        =>  $data['name'],
            'identifier'  =>  $data['identifier'],
        );

        if ( $data['identifier'] )
        {
            $identifiers[$data['identifier']] = $data['id'];
        }

        $nextId = max( $nextId, $data['id'] );
    }

    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Values\Content\Section', $sections ),
        generateMapping( $identifiers ),
        $nextId
    );
}

function generateContentInfoFixture( array $fixture )
{
    $nextId       = 0;
    $contentInfos = array();

    $indexMap = array();

    $languageCodes = array();
    foreach ( getFixtureTable( 'ezcontent_language', $fixture ) as $data )
    {
        $languageCodes[$data['id']] = $data['locale'];
    }

    $mainLocationIds = array();
    foreach ( getFixtureTable( 'ezcontentobject_tree', $fixture ) as $data )
    {
        $mainLocationIds[$data['contentobject_id']] = $data['main_node_id'];
    }

    foreach ( getFixtureTable( 'ezcontentobject', $fixture ) as $data )
    {
        $indexMap[$data['remote_id']] = array(
            'versionId'  =>  array(),
            'contentId'  =>  array(),
        );

        $indexMap[$data['id']] = array(
            'versionId'  =>  array(),
            'contentId'  =>  array(),
        );

        $contentInfos[$data['id']] = array(
            'contentId'         =>  $data['id'],
            'name'              =>  $data['name'],
            'contentTypeId'     =>  $data['contentclass_id'],
            'sectionId'         =>  $data['section_id'],
            'currentVersionNo'  =>  $data['current_version'],
            'published'         =>  ( $data['published'] != 0 ),
            'ownerId'           =>  $data['owner_id'],
            'modificationDate'  =>  'new \DateTime( "@' . $data['modified'] . '" )',
            'publishedDate'     =>  'new \DateTime( "@' . $data['published'] . '" )',
            'alwaysAvailable'   =>  (boolean) ( $data['language_mask'] & 1 ),
            'remoteId'          =>  $data['remote_id'],
            'mainLanguageCode'  =>  $languageCodes[$data['initial_language_id']],
            'repository'        =>  '$this',
            'mainLocationId'    =>  $mainLocationIds[$data['id']],
        );
        $nextId = max( $nextId, $data['id'] );
    }

    list( $fieldDef ) = getContentTypeFieldDefinition( $fixture );

    $fields      = array();
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
            'id'                  =>  $data['id'],
            'value'               =>  $value,
            'languageCode'        =>  $data['language_code'],
            'fieldDefIdentifier'  =>  $identifier,

            'contentId'           =>  $data['contentobject_id'],
            'version'             =>  $data['version']
        );

        $fieldNextId = max( $fieldNextId, $data['id'] );
    }

    $content       = array();
    $versionInfo   = array();
    $versionNextId = 0;
    foreach ( getFixtureTable( 'ezcontentobject_version', $fixture ) as $data )
    {
        $versionInfo[$data['id']] = array(
            'id'                   =>  $data['id'],
            'contentId'            =>  $data['contentobject_id'],
            'status'               =>  $data['status'] <= 2 ? $data['status'] : 1,
            'versionNo'            =>  $data['version'],
            'modificationDate'     =>  'new \DateTime( "@' . $data['modified'] . '" )',
            'creatorId'            =>  $data['creator_id'],
            'creationDate'         =>  'new \DateTime( "@' . $data['created'] . '" )',
            'initialLanguageCode'  =>  $languageCodes[$data['initial_language_id']],
            'languageCodes'        =>  array(), // TODO: Extract language codes from fields
            'repository'           =>  '$this',
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
            'contentId'      =>  $data['contentobject_id'],
            'contentTypeId'  =>  $contentInfos[$data['contentobject_id']]['contentTypeId'],
            'fields'         =>  $contentFields,
            'relations'      =>  array(),

            'versionNo'      =>  $data['version'],
            'repository'     =>  '$this'
        );

        $remoteId = $contentInfos[$data['contentobject_id']]['remoteId'];

        $versionId = $data['id'];
        $contentId = $data['contentobject_id'];

        $indexMap[$remoteId]['versionId'][$versionId]  = $versionId;
        $indexMap[$remoteId]['contentId'][$contentId]  = $contentId;
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
        if ( $content1['contentId'] === $content2['contentId'] )
        {
            return $content2['versionNo'] - $content1['versionNo'];
        }
        return $content1['contentId'] - $content2['contentId'];
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
    $nextId    = 0;
    $locations = array();

    foreach ( getFixtureTable( 'ezcontentobject_tree', $fixture ) as $data )
    {
        $locations[$data['node_id']] = array(
            'id'          => $data['node_id'],
            'priority'    => $data['priority'],
            'hidden'      => (bool) $data['is_hidden'],
            'invisible'   => (bool) $data['is_invisible'],
            'remoteId'    => $data['remote_id'],
            'contentInfo' => ( $data['node_id'] == 1 ? null :createRepoCall(
                'ContentService',
                'loadContentInfo',
                array( $data['contentobject_id'] )
            ) ),
            'parentLocationId'        => $data['parent_node_id'],
            'pathString'              => $data['path_string'],
            'modifiedSubLocationDate' => $data['modified_subnode'],
            'depth'                   => $data['depth'],
            'sortField'               => $data['sort_field'],
            'sortOrder'               => $data['sort_order'],
            'childCount'              => null, // Cannot be easily calculated

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

function generateLanguageFixture( array $fixture )
{
    $nextId        = 0;
    $languages     = array();
    $languageCodes = array();

    foreach ( getFixtureTable( 'ezcontent_language', $fixture ) as $data )
    {
        $languages[$data['id']] = array(
            'id'            =>  $data['id'],
            'name'          =>  $data['name'],
            'enabled'       =>  !$data['disabled'],
            'languageCode'  =>  $data['locale']
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
            'id'             =>  $data['contentobject_id'],
            'login'          =>  $data['login'],
            'email'          =>  $data['email'],
            'passwordHash'   =>  $data['password_hash'],
            'hashAlgorithm'  =>  $data['password_hash_type'],
            'isEnabled'      =>  true,
            'content'         =>  '$this->getContentService()->loadContent( ' . $data['contentobject_id'] . ' )'
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

    $node2parentId  = array();
    $content2nodeId = array();
    foreach ( getFixtureTable( 'ezcontentobject_tree', $fixture ) as $data )
    {
        $content2nodeId[$data['contentobject_id']] = $data['node_id'];
        $node2parentId[$data['node_id']]           = $data['parent_node_id'];
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
            'id'              =>  $data['id'],
            'parentId'        =>  is_numeric( $parentId ) ? $parentId : 'null',
            'subGroupCount'   =>  0,
            'content'         =>  '$this->getContentService()->loadContent( ' . $data['id'] . ' )'
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
    $names  = array();
    $roles  = array();

    foreach ( getFixtureTable( 'ezrole', $fixture ) as $data )
    {
        $roles[$data['id']] = array(
            'id'            =>  $data['id'],
            'identifier'    =>  $data['name']
        );

        $names[$data['name']] = $data['id'];

        $nextId = max( $nextId, $data['id'] );
    }

    $content2role    = array();
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
            'id'          =>  $data['id'],
            'roleId'      =>  $data['role_id'],
            'contentId'   =>  $data['contentobject_id'],
            'identifier'  =>  $data['limit_identifier'],
            'value'       =>  $data['limit_value']
        );
    }

    $role2policy  = array();
    $policies     = array();
    $policyNextId = 0;
    foreach ( getFixtureTable( 'ezpolicy', $fixture ) as $data )
    {
        if ( false === isset( $role2policy[$data['role_id']] ) )
        {
            $role2policy[$data['role_id']] = array();
        }
        $role2policy[$data['role_id']][] = $data['id'];

        $policies[$data['id']] = array(
            'id'           =>  $data['id'],
            'roleId'       =>  $data['role_id'],
            'module'       =>  $data['module_name'],
            'function'     =>  $data['function_name'],
            'limitations'  =>  array()
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

function getUser2GroupMapping( array $fixture )
{
    $users = array();
    foreach ( getFixtureTable( 'ezuser', $fixture ) as $data )
    {
        $users[] = (int) $data['contentobject_id'];
    }

    $nodes    = array();
    $contents = array();
    foreach ( getFixtureTable( 'ezcontentobject_tree', $fixture ) as $data )
    {
        $nodes[$data['node_id']]    = $data['parent_node_id'];
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
        $code .= '        "' . $key . '"  =>  ' . valueToString( $value ) . ',' . PHP_EOL;
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
    $id = isset( $object['id'] ) ? $object['id'] : $object['contentId'];
    $code = '        ' . $id . '  =>  new ' . $class . '(' . PHP_EOL .
            '            array(' . PHP_EOL;
    foreach ( $object as $name => $value )
    {

        $code .= '                "' . $name . '"  =>  ' . valueToString( $value ) . ',' . PHP_EOL;
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
