<?php
/**
 * File containing the IOServiceFactory class.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\FieldType\Image\IO;

use eZ\Publish\Core\FieldType\Image\IO\Legacy as LegacyImageIOService;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Factory for the Legacy Image IOService.
 * Sets options using the ConfigResolver.
 */
class IOServiceFactory
{
    /** @var \eZ\Publish\Core\IO\IOServiceInterface */
    private $publishedIOService;

    /** @var \eZ\Publish\Core\IO\IOServiceInterface */
    private $draftIOService;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function __construct( IOServiceInterface $publishedIOService, IOServiceInterface $draftIOService, ConfigResolverInterface $configResolver )
    {
        $this->draftIOService = $draftIOService;
        $this->publishedIOService = $publishedIOService;
        $this->configResolver = $configResolver;
    }

    public function buildService()
    {
        $options = array(
            'var_dir' => $this->configResolver->getParameter( 'var_dir' ),
            'storage_dir' => $this->configResolver->getParameter( 'storage_dir' ),
            'draft_images_dir' => $this->configResolver->getParameter( 'image.versioned_images_dir' ),
            'published_images_dir' => $this->configResolver->getParameter( 'image.published_images_dir' )
        );

        return new LegacyImageIOService( $this->publishedIOService, $this->draftIOService, $options );
    }
}
