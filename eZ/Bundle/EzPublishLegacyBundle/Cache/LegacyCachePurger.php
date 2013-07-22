<?php
/**
 * File containing the LegacyCachePurger class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Cache;

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

    public function __construct( \Closure $legacyKernelClosure )
    {
        $this->legacyKernelClosure = $legacyKernelClosure;
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
                            'description' => "eZ Publish Cache Handler\n" .
                                             "Allows for easy clearing of Cache files\n",
                            'use-session' => false,
                            'use-modules' => false,
                            'use-extensions' => true
                        )
                    )
                );
                $helper->clearItems( eZCache::fetchList(), false );
            },
            false
        );
    }
}
