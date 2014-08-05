<?php
/**
 * File containing the LegacyCachePurger class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Cache;

use eZ\Bundle\EzPublishLegacyBundle\LegacyMapper\Configuration;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use eZCacheHelper;
use eZCLI;
use eZScript;
use eZCache;

/**
 * Purger for legacy cache.
 * Hooks into cache:clear command.
 */
class LegacyCachePurger implements CacheClearerInterface
{
    /**
     * @var \Closure
     */
    private $legacyKernelClosure;

    public function __construct(
        \Closure $legacyKernelClosure,
        Configuration $configurationMapper,
        Filesystem $fs,
        $legacyRootDir,
        SiteAccess $siteAccess
    )
    {
        $this->legacyKernelClosure = $legacyKernelClosure;

        // If ezp_extension.php doesn't exist or siteaccess name is "setup", it means that eZ Publish is not yet installed.
        // Hence we deactivate configuration mapper to avoid potential issues (e.g. ezxFormToken which cannot be loaded).
        if ( !$fs->exists( "$legacyRootDir/var/autoload/ezp_extension.php" ) || $siteAccess->name === 'setup' )
        {
            $configurationMapper->setEnabled( false );
        }
    }

    /**
     * @return \ezpKernelHandler
     */
    private function getLegacyKernel()
    {
        $closure = $this->legacyKernelClosure;
        return $closure();
    }

    /**
     * Clears any caches necessary.
     *
     * @param string $cacheDir The cache directory.
     */
    public function clear( $cacheDir )
    {
        $this->getLegacyKernel()->runCallback(
            function ()
            {
                $helper = new eZCacheHelper(
                    $cli = eZCLI::instance(),
                    $script = eZScript::instance(
                        array(
                            'description' => "eZ Publish Cache Handler",
                            'use-session' => false,
                            'use-modules' => false,
                            'use-extensions' => true
                        )
                    )
                );
                $helper->clearItems( eZCache::fetchList(), false );
            },
            false,
            false
        );
    }
}
