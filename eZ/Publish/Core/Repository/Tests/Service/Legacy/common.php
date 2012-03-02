<?php
/**
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Legacy;
use eZ\Publish\Core\Repository\Repository,
    eZ\Publish\Core\IO\LegacyHandler as LegacyIoHandler,
    eZ\Publish\Core\Persistence\Legacy\Handler as LegacyPersistenceHandler,
    ReflectionMethod;


/**
 * Common init code for legacy service tests, returns repository
 */

$dsn = ( isset( $_ENV['DATABASE'] ) && $_ENV['DATABASE'] ) ? $_ENV['DATABASE'] : 'sqlite://:memory:';
$db = preg_replace( '(^([a-z]+).*)', '\\1', $dsn );

$legacyHandler = new LegacyPersistenceHandler(
    array(
        'dsn' => $dsn,
        'defer_type_update' => false,
        'external_storage' => array(
            'ezauthor' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
            'ezbinaryfile' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\BinaryFileStorage',
            'ezboolean' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
            'ezcountry' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
            'ezdatetime' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
            'ezemail' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
            'ezfloat' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
            //'ezimage' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\ImageStorage',
            'ezinteger' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
            //'ezkeyword' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\KeywordStorage',
            'ezmedia' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\MediaStorage',
            //'ezobjectrelationlist' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\ObjectRelationListStorage',
            'ezpage' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
            'ezselection' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
            'ezstring' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
            'ezsrrating' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
            'eztext' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
            'ezurl' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\UrlStorage',
            'ezuser' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
            'ezxmltext' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage'
        ),
        'field_converter' => array(
            'ezauthor' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\TextLine',
            'ezbinaryfile' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\BinaryFile',
            'ezboolean' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Checkbox',
            'ezcountry' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Country',
            'ezdatetime' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezemail' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\TextLine',
            //'ezfloat' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Float',
            //'ezimage' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Image',
            'ezinteger' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezkeyword' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\TextLine',
            'ezmedia' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Media',
            //'ezobjectrelationlist' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\ObjectRelationList',
            'ezpage' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\TextLine',
            'ezselection' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Selection',
            'ezstring' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\TextLine',
            'ezsrrating' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Rating',
            'eztext' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\TextBlock',
            'ezurl' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Url',
            'ezuser' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezxmltext' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\XmlText',
         )
    )
);

// Get access to ezc DB handler
    $refGetDatabase = new ReflectionMethod( $legacyHandler, 'getDatabase' );
    $refGetDatabase->setAccessible( true );
    $handler = $refGetDatabase->invoke( $legacyHandler );


// Find Persistence dir
    if ( file_exists( 'eZ/Publish/Core/Persistence/' ) )
        $legacyHandlerDir = "eZ/Publish/Core/Persistence";
    else if ( file_exists( 'extension/api/eZ/Publish/Core/Persistence/' ) )
        $legacyHandlerDir = "extension/api/eZ/Publish/Core/Persistence";
    else
        throw new \Exception( 'Could not find Legacy dir, skipping' );


// Insert Schema
    $schema = $legacyHandlerDir . '/Legacy/Tests/_fixtures/schema.' . $db . '.sql';
    $queries = array_filter( preg_split( '(;\\s*$)m', file_get_contents( $schema ) ) );
    foreach ( $queries as $query )
    {
        $handler->exec( $query );
    }


// Insert some default data
    $data = require __DIR__ . '/_fixtures/full_dump.php';
    //$data =  require __DIR__ . '/_fixtures/mini_dump.php';
    foreach ( $data as $table => $rows )
    {
        // Check that at least one row exists
        if ( !isset( $rows[0] ) )
        {
            continue;
        }

        $q = $handler->createInsertQuery();
        $q->insertInto( $handler->quoteIdentifier( $table ) );

        // Contains the bound parameters
        $values = array();

        // Binding the parameters
        foreach ( $rows[0] as $col => $val )
        {
            $q->set(
                $handler->quoteIdentifier( $col ),
                $q->bindParam( $values[$col] )
            );
        }

        $stmt = $q->prepare();

        foreach ( $rows as $row )
        {
            try
            {
                // This CANNOT be replaced by:
                // $values = $row
                // each $values[$col] is a PHP reference which should be
                // kept for parameters binding to work
                foreach ( $row as $col => $val )
                {
                    $values[$col] = $val;
                }

                $stmt->execute();
            }
            catch ( \Exception $e )
            {
                echo "$table ( ", implode( ', ', $row ), " )\n";
                throw $e;
            }
        }
    }



    if ( $db === 'sqlite' )
    {
        // We need trigger for SQLite, as it does not support a multicolumn key with one of them being set to auto-increment.
        $handler->exec(
            'CREATE TRIGGER my_ezcontentobject_attribute_increment
            AFTER INSERT
            ON ezcontentobject_attribute
            BEGIN
                UPDATE ezcontentobject_attribute SET id = (SELECT MAX(id) FROM ezcontentobject_attribute) + 1  WHERE rowid = new.rowid AND id = 0;
            END;'
        );
    }
    else if ( $db === 'pgsql' )
    {
        // Update PostgreSQL sequences
        $queries = array_filter( preg_split( '(;\\s*$)m', file_get_contents( $legacyHandlerDir . '/Legacy/Tests/_fixtures/setval.pgsql.sql' ) ) );
        foreach ( $queries as $query )
        {
            $handler->exec( $query );
        }
    }

return new Repository( $legacyHandler, new LegacyIoHandler() );
