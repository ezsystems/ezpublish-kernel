<?php
/**
 * File containing the Configurator class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor;

/**
 * Configurator for the Handler
 */
class Configurator
{
    /**
     * Configuration array
     *
     * as described in
     * {@link \eZ\Publish\Core\Persistence\Legacy\Handler}.
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
     * @param \eZ\Publish\Core\Persistence\Legacy\StorageRegistry $registry
     * @return void
     */
    public function configureExternalStorages( StorageRegistry $registry )
    {
        if ( isset( $this->config['external_storage'] ) )
        {
            foreach ( $this->config['external_storage'] as $typeName => $class )
            {
                $registry->register( $typeName, new $class() );
            }
        }
    }

    /**
     * Configures the field value converter registry
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\FieldValue\Converter\Registry $registry
     * @return void
     */
    public function configureFieldConverter( Registry $registry )
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
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor $processor
     * @return void
     */
    public function configureTransformationRules( TransformationProcessor $processor )
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
