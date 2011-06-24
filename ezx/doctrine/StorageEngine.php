<?php
/**
 * Storage Engine implementation for doctrine
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */

namespace ezx\doctrine;
class StorageEngine implements \ezp\base\StorageEngineInterface
{
    /**
     * Instances of handlers
     *
     * @var \ezx\base\Interfaces\StorageEngine\Handler[]
     */
    protected $handlers = array();

    /**
     * Object for doctrine backend
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * Setups current instance and doctrine object
     */
    public function __construct()
    {
        require 'Doctrine/Common/ClassLoader.php';
        $classLoader = new \Doctrine\Common\ClassLoader('Doctrine');
        $classLoader->register(); // register on SPL autoload stack

        $cwd = getcwd();
        if ( !is_dir( $cwd . '/var/cache/Proxies' ) )
            mkdir( "$cwd/var/cache/Proxies/", 0777 , true );// Seeing Protocol error? Try renaming ezp-next to next..

        $devMode = \ezp\base\Configuration::developmentMode();
        $config = new \Doctrine\ORM\Configuration();
        $config->setProxyDir( $cwd . '/var/cache/Proxies' );
        $config->setProxyNamespace('ezx\doctrine');
        $config->setAutoGenerateProxyClasses( $devMode );

        $driverImpl = $config->newDefaultAnnotationDriver( $cwd . '/ezx/' );
        $config->setMetadataDriverImpl( $driverImpl );

        if ( $devMode )
            $cache = new \Doctrine\Common\Cache\ArrayCache();
        else
            $cache = new \Doctrine\Common\Cache\ApcCache();

        $config->setMetadataCacheImpl( $cache );
        $config->setQueryCacheImpl( $cache );

        $evm = new \Doctrine\Common\EventManager();
        $settings = \ezp\base\Configuration::getInstance()->getSection( 'doctrine' );
        $this->em =  \Doctrine\ORM\EntityManager::create( $settings, $config, $evm );
    }

    /**
     * Get Content Handler
     *
     * @uses handler()
     * @return \ezp\base\StorageEngine\ContentHandlerInterface
     */
    public function getContentHandler()
    {
        return $this->handler( '\ezx\doctrine\ContentHandler' );
    }

    /**
     * Get ContentType Handler
     *
     * @uses handler()
     * @return \ezp\base\StorageEngine\ContentTypeHandlerInterface
     */
    public function getContentTypeHandler()
    {
        return $this->handler( '\ezx\doctrine\ContentTypeHandler' );
    }

    /**
     * Get Content Location Handler
     *
     * @return \ezp\base\StorageEngine\ContentLocationHandlerInterface
     */
    public function getContentLocationHandler()
    {
        return $this->handler( '\ezx\doctrine\ContentLocationHandler' );
    }

    /**
     * Get User Handler
     *
     * @return \ezp\base\StorageEngine\UserHandlerInterface
     */
    public function getUserHandler()
    {
        return $this->handler( '\ezx\doctrine\UserHandler' );
    }

    /**
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction(){}

    /**
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function commit(){}

    /**
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function rollback(){}

     /**
     * Get/create instance of handler objects
     *
     * @param string $className
     * @return \ezx\base\Interfaces\StorageEngine\Handler
     * @throws RuntimeException
     */
    protected function handler( $className )
    {
        if ( isset( $this->handlers[$className] ) )
            return $this->handlers[$className];

        if ( class_exists( $className ) )
            return $this->handlers[$className] = new $className( $this, $this->em );

        throw new \RuntimeException( "Could not load '$className' handler!" );
    }
}
