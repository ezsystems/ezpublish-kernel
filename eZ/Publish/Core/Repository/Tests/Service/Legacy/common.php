<?php
/**
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
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
            'ezauthor' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezbinaryfile' => 'eZ\\Publish\\Core\\FieldType\\BinaryFile\\BinaryFileStorage',
            'ezboolean' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezcountry' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezdatetime' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezemail' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezfloat' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            //'ezimage' => 'eZ\\Publish\\Core\\FieldType\\Image\\ImageStorage',
            'ezimage' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezinteger' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            //'ezkeyword' => 'eZ\\Publish\\Core\\FieldType\\Keyword\\KeywordStorage',
            'ezmedia' => 'eZ\\Publish\\Core\\FieldType\\Media\\MediaStorage',
            //'ezobjectrelationlist' => 'eZ\\Publish\\Core\\FieldType\\Converter\\ObjectRelationListStorage',
            'ezpage' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezselection' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezstring' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezsrrating' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'eztext' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezurl' => 'eZ\\Publish\\Core\\FieldType\\Url\\UrlStorage',
            'ezuser' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezxmltext' => 'eZ\\Publish\\Core\\FieldType\\NullStorage'

            // @TODO: A bunch of faked external storages to get search tests
            // working
            'ezimage' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezprice' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezpackage' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezmultioption' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezinisetting' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezobjectrelation' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezobjectrelationlist' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
            'ezsubtreesubscription' => 'eZ\\Publish\\Core\\FieldType\\NullStorage',
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
            'ezimage' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezinteger' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezkeyword' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Keyword',
            'ezmedia' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Media',
            //'ezobjectrelationlist' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\ObjectRelationList',
            'ezobjectrelation' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezpage' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\TextLine',
            'ezselection' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Selection',
            'ezstring' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\TextLine',
            'ezsrrating' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Rating',
            'eztext' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\TextBlock',
            'ezurl' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Url',
            'ezuser' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezxmltext' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\XmlText',

            // @TODO: A bunch of faked converters to get search tests working
            'ezimage' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezprice' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezpackage' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezoption' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezmultioption' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezinisetting' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezobjectrelation' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezobjectrelationlist' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezsubtreesubscription' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
            'ezgmaplocation' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
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
    //$data = require __DIR__ . '/_fixtures/mini_dump.php';
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

    if ( $db === 'pgsql' )
    {
        // Update PostgreSQL sequences
        $queries = array_filter( preg_split( '(;\\s*$)m', file_get_contents( $legacyHandlerDir . '/Legacy/Tests/_fixtures/setval.pgsql.sql' ) ) );
        foreach ( $queries as $query )
        {
            $handler->exec( $query );
        }
    }

$serviceSettings = array(
    'fieldType' => array(
        'ezauthor' => function(){ return new \eZ\Publish\Core\FieldType\Author\Type(
            new \eZ\Publish\Core\Repository\ValidatorService(),
            new \eZ\Publish\Core\Repository\FieldTypeTools()
        ); },
        'ezdatetime' => function(){ return new \eZ\Publish\Core\FieldType\DateAndTime\Type(
            new \eZ\Publish\Core\Repository\ValidatorService(),
            new \eZ\Publish\Core\Repository\FieldTypeTools()
        ); },
        'ezfloat' => function(){ return new \eZ\Publish\Core\FieldType\Float\Type(
            new \eZ\Publish\Core\Repository\ValidatorService(),
            new \eZ\Publish\Core\Repository\FieldTypeTools()
        ); },
        'ezinteger' => function(){ return new \eZ\Publish\Core\FieldType\Integer\Type(
            new \eZ\Publish\Core\Repository\ValidatorService(),
            new \eZ\Publish\Core\Repository\FieldTypeTools()
        ); },
        'ezkeyword' => function(){ return new \eZ\Publish\Core\FieldType\Keyword\Type(
            new \eZ\Publish\Core\Repository\ValidatorService(),
            new \eZ\Publish\Core\Repository\FieldTypeTools()
        ); },
        'eztext' => function(){ return new \eZ\Publish\Core\FieldType\TextBlock\Type(
            new \eZ\Publish\Core\Repository\ValidatorService(),
            new \eZ\Publish\Core\Repository\FieldTypeTools()
        ); },
        'ezstring' => function(){ return new \eZ\Publish\Core\FieldType\TextLine\Type(
            new \eZ\Publish\Core\Repository\ValidatorService(),
            new \eZ\Publish\Core\Repository\FieldTypeTools()
        ); },
        'ezimage' => function(){ return new \eZ\Publish\Core\FieldType\Integer\Type(
            new \eZ\Publish\Core\Repository\ValidatorService(),
            new \eZ\Publish\Core\Repository\FieldTypeTools()
        ); },
        'ezuser' => function(){ return new \eZ\Publish\Core\FieldType\Integer\Type(
            new \eZ\Publish\Core\Repository\ValidatorService(),
            new \eZ\Publish\Core\Repository\FieldTypeTools()
        ); },
        'ezurl' => function(){ return new \eZ\Publish\Core\FieldType\Url\Type(
            new \eZ\Publish\Core\Repository\ValidatorService(),
            new \eZ\Publish\Core\Repository\FieldTypeTools()
        ); },
        'ezxmltext' => function(){ return new \eZ\Publish\Core\FieldType\XmlText\Type(
            new \eZ\Publish\Core\FieldType\XmlText\Input\Parser\Simplified(
                new \eZ\Publish\Core\FieldType\XmlText\Schema
            )
        ); },
    )
);

return new Repository( $legacyHandler, new LegacyIoHandler(), ( isset( $serviceSettings ) ? $serviceSettings : array() ) );
