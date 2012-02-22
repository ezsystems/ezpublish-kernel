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
writeFixtureFile( generateSectionFixture( $fixture ), 'Section', $argv[2] );
writeFixtureFile( generateLanguageFixture( $fixture ), 'Language', $argv[2] );
writeFixtureFile( generateUserFixture( $fixture ), 'User', $argv[2] );
writeFixtureFile( generateUserGroupFixture( $fixture ), 'UserGroup', $argv[2] );
writeFixtureFile( generateRoleFixture( $fixture ), 'Role', $argv[2] );

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
    $nextId = 0;
    $users  = array();
    foreach ( getFixtureTable( 'ezuser', $fixture ) as $data )
    {
        $users[] = array(
            'id'             =>  $data['contentobject_id'],
            'login'          =>  $data['login'],
            'email'          =>  $data['email'],
            'passwordHash'   =>  $data['password_hash'],
            'hashAlgorithm'  =>  $data['password_hash_type'],
            'isEnabled'      =>  true
        );
        $nextId = max( $nextId, $data['contentobject_id'] );
    }
    
    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserStub', $users ),
        $nextId
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

    $nextId = 0;
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
            'id'             =>  $data['id'],
            'parentId'       =>  is_numeric( $parentId ) ? $parentId : 'null',
            'subGroupCount'  =>  0
        );

        $nextId = max( $nextId, $data['id'] );
    }

    foreach ( $groups as $group )
    {
        if ( isset( $groups[$group['parentId']] ) )
        {
             ++$groups[$group['parentId']]['subGroupCount'];
        }
    }


    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub', $groups ),
        $nextId
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

    return generateReturnArray(
        generateValueObjects( '\eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleStub', $roles ),
        generateMapping( $names ),
        $nextId
    );
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
    $code = '        ' . $object['id'] . '  =>  new ' . $class . '(' . PHP_EOL .
            '            array(' . PHP_EOL;
    foreach ( $object as $name => $value )
    {

        $code .= '                "' . $name . '"  =>  ' . valueToString( $value ) . ',' . PHP_EOL;
    }

    $code .= '            )' . PHP_EOL .
             '        ),' . PHP_EOL;

    return $code;
}

function valueToString( $value )
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
    else if ( is_string( $value ) && 0 !== strpos( $value, 'new \\' ) )
    {
        $value = '"' . $value . '"';
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