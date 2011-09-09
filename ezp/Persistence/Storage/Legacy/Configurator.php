<?php
/**
 * File containing the Configurator class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy;

/**
 * Configurator for the RepositoryHandler
 */
class Configurator
{
    /**
     * Configuration array
     *
     * as described in
     * {@link \ezp\Persistence\Storage\Legacy\RepositoryHandler}.
     *
     * @var string[][]
     */
    protected $config;

    /**
     * Creates a new configurator
     *
     * @param string[][] $config
     */
    public function __construct( array $config )
    {
        $this->config = $config;
    }

    /**
     * Returns the data source name
     *
     * @return string
     */
    public function getDsn()
    {
        if ( !isset( $this->config['dsn'] ) )
        {
            throw new \RuntimeException( 'Missing "dsn" config value.' );
        }
        return $this->config['dsn'];
    }

    /**
     * Returns if the updating of types should be deferred
     *
     * @return bool
     */
    public function shouldDeferTypeUpdates()
    {
        return ( isset( $this->config['defer_type_update'] )
            && $this->config['defer_type_update'] );
    }

    /**
     * Configurs the storage registry
     *
     * @param \ezp\Persistence\Storage\Legacy\StorageRegistry $registry
     * @return void
     */
    public function configureExternalStorages( Content\StorageRegistry $registry )
    {
        if ( isset( $this->config['external_storages'] ) )
        {
            foreach ( $this->config['external_storages'] as $typeName => $class )
            {
                $registry->register( $typeName, new $class() );
            }
        }
    }

    /**
     * Configures the field value converter registry
     *
     * @param \ezp\Persistence\Storage\Legacy\FieldValue\Converter\Registry $registry
     * @return void
     */
    public function configureFieldConverter( Content\FieldValue\Converter\Registry $registry )
    {
        if ( isset( $this->config['field_converter'] ) )
        {
            foreach ( $this->config['field_converter'] as $typeName => $class )
            {
                $registry->register( $typeName, new $class() );
            }
        }
    }

    /**
     * Configures the search transformation processor
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Search\TransformationProcessor $processor
     * @return void
     */
    public function configureTransformationRules( Content\Search\TransformationProcessor $processor )
    {
        if ( isset( $this->config['transformation_rule_files'] ) )
        {
            foreach ( $this->config['transformation_rule_files'] as $ruleFile )
            {
                $processor->loadRules( $ruleFile );
            }
        }
    }
}
