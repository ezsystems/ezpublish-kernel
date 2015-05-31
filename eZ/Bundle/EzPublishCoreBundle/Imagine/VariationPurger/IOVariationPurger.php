<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger;

use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\SPI\Variation\VariationPurger;

/**
 * Purges image variations using the IOService.
 *
 * Depends on aliases being stored in their own folder, with each alias folder mirroring the original files structure.
 */
class IOVariationPurger implements VariationPurger
{
    /** @var \eZ\Publish\Core\IO\IOServiceInterface */
    private $io;

    public function __construct( IOServiceInterface $io )
    {
        $this->io = $io;
    }

    public function purge( array $aliasNames )
    {
        foreach ( $aliasNames as $aliasName )
        {
            $this->io->deleteDirectory( $aliasName );
        }
    }
}
