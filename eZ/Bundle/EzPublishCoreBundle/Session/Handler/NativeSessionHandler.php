<?php

/**
 * File containing the NativeSessionHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Session\Handler;

/**
 * Class NativeSessionHandler.
 *
 * This class makes it possible to configure the native PHP session handler.
 */
class NativeSessionHandler extends \SessionHandler
{
    /**
     * @param string $savePath      Path of directory to save session files
     *                              Default null will leave setting as defined by PHP.
     *                              '/path', 'host:port'
     * @param string $saveHandler   Could be any handler supported by php
     *                              Default null will leave setting as defined by PHP.
     *                              'redis', 'file'
     *
     * @see http://php.net/manual/en/session.configuration.php#ini.session.save-path for further details.
     */
    public function __construct($savePath = null, $saveHandler = null)
    {
        if (null !== $savePath) {
            ini_set('session.save_path', $savePath);
        }

        if (null !== $saveHandler) {
            ini_set('session.save_handler', $saveHandler);
        }
    }
}
