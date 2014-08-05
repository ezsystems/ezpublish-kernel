<?php
/**
 * File containing the LegacyKernel class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy;

use Exception;
use ezpKernel;
use ezpKernelHandler;
use ezxFormToken;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Class wrapping the legacy kernel
 */
class Kernel extends ezpKernel
{
    /**
     * Legacy root directory
     *
     * @var string
     */
    private $legacyRootDir;

    /**
     * Original webroot directory
     *
     * @var string
     */
    private $webRootDir;

    private $runningCallback = false;

    /**
     * Directory where kernel was originally running from.
     * @see self::runCallback()
     *
     * @var string
     */
    private $previousRunningDir;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \ezpKernelHandler $kernelHandler
     * @param string $legacyRootDir Must be a absolute dir
     * @param string $webRootDir Must be a absolute dir
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct( ezpKernelHandler $kernelHandler, $legacyRootDir, $webRootDir, LoggerInterface $logger = null )
    {
        $this->legacyRootDir = $legacyRootDir;
        $this->webRootDir = $webRootDir;
        $this->logger = $logger;

        $this->enterLegacyRootDir();
        parent::__construct( $kernelHandler );
        $this->leaveLegacyRootDir();
        $this->setUseExceptions( true );
    }

    /**
     * Checks if LegacyKernel has already been instantiated.
     *
     * @return bool
     */
    public static function hasInstance()
    {
        return static::$instance !== null;
    }

    public static function resetInstance()
    {
        static::$instance = null;
    }

    /**
     * Changes the current working directory to the legacy root dir.
     * Calling this method is mandatory to use legacy kernel since a lot of resources in eZ Publish 4.x relatively defined.
     */
    public function enterLegacyRootDir()
    {
        $this->previousRunningDir = getcwd();
        if ( $this->logger )
        {
            $this->logger->debug( "Legacy kernel: Leaving '$this->previousRunningDir' for '$this->legacyRootDir'" );
        }
        chdir( $this->legacyRootDir );
    }

    /**
     * Leaves the legacy root dir and switches back to the dir where execution was happening before we entered LegacyRootDir.
     */
    public function leaveLegacyRootDir()
    {
        $previousDir = $this->previousRunningDir;
        if ( !$previousDir )
        {
            if ( $this->logger )
            {
                $this->logger->warning(
                    "Trying to leave legacy root dir without a previously executing dir. Falling back to '$this->webRootDir'"
                );
            }
            $previousDir = $this->webRootDir;
        }

        $this->previousRunningDir = null;
        if ( $this->logger )
        {
            $this->logger->debug( "Legacy kernel: Leaving '$this->legacyRootDir' for '$previousDir'" );
        }
        chdir( $previousDir );
    }

    /**
     * Runs current request through legacy kernel.
     *
     * @return \ezpKernelResult
     */
    public function run()
    {
        $this->enterLegacyRootDir();
        $return = parent::run();
        $this->leaveLegacyRootDir();
        return $return;
    }

    /**
     * Runs a callback function in the legacy kernel environment.
     * This is useful to run eZ Publish 4.x code from a non-related context (like eZ Publish 5).
     * Will throw a \RuntimeException if trying to run a callback inside a callback.
     *
     * @param \Closure $callback
     * @param boolean $postReinitialize Default is true.
     *                               If set to false, the kernel environment will not be reinitialized.
     *                               This can be useful to optimize several calls to the kernel within the same context.
     *
     * @param bool|null $formTokenEnable Force ezxFormToken to be enabled or disabled, use system settings when null
     *
     * @throws \RuntimeException
     * @throws \Exception
     * @return mixed The result of the callback
     */
    public function runCallback( \Closure $callback, $postReinitialize = true, $formTokenEnable = null )
    {
        if ( $this->runningCallback )
        {
            throw new RuntimeException( 'Trying to run recursive callback in legacy kernel! Inception!' );
        }

        $this->runningCallback = true;
        $this->enterLegacyRootDir();

        if ( $formTokenEnable !== null && class_exists( 'ezxFormToken' ) )
        {
            $formTokenWasEnabled = ezxFormToken::isEnabled();
            ezxFormToken::setIsEnabled( $formTokenEnable );
        }

        try
        {
            $return = parent::runCallback( $callback, $postReinitialize );
        }
        catch ( Exception $e )
        {
            $this->leaveLegacyRootDir();
            $this->runningCallback = false;
            throw $e;
        }

        if ( isset( $formTokenWasEnabled ) )
        {
            ezxFormToken::setIsEnabled( $formTokenWasEnabled );
        }

        $this->leaveLegacyRootDir();
        $this->runningCallback = false;
        return $return;
    }
}
