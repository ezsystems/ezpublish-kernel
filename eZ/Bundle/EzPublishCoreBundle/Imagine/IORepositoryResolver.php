<?php
/**
 * File containing the IOCacheResolver class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\IO\Values\MissingBinaryFile;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

/**
 * LiipImagineBundle cache resolver using eZ IO repository.
 */
class IORepositoryResolver implements ResolverInterface
{
    const VARIATION_ORIGINAL = 'original';

    /**
     * @var \eZ\Publish\Core\IO\IOServiceInterface
     */
    private $ioService;

    /**
     * @var FilterConfiguration
     */
    private $filterConfiguration;

    public function __construct( IOServiceInterface $ioService, FilterConfiguration $filterConfiguration )
    {
        $this->ioService = $ioService;
        $this->filterConfiguration = $filterConfiguration;
    }

    public function isStored( $path, $filter )
    {
        return $this->ioService->exists( $this->getFilePath( $path, $filter ) );
    }

    public function resolve( $path, $filter )
    {
        try
        {
            $binaryFile = $this->ioService->loadBinaryFile( $path );
            // Treat a MissingBinaryFile as a not loadable file.
            if ( $binaryFile instanceof MissingBinaryFile )
            {
                throw new NotResolvableException( "Variation image not found in $path" );
            }

            $path = $binaryFile->uri;
            return $filter !== static::VARIATION_ORIGINAL ? $this->getFilePath(
                $path,
                $filter
            ) : $path;
        }
        catch ( NotFoundException $e )
        {
            throw new NotResolvableException( "Variation image not found in $path", 0, $e );
        }
    }

    /**
     * Stores image alias in the IO Repository.
     * A temporary file is created to dump the filtered image and is used as basis for creation in the IO Repository.
     *
     * {@inheritDoc}
     */
    public function store( BinaryInterface $binary, $path, $filter )
    {
        $tmpFile = tmpfile();
        fwrite( $tmpFile, $binary->getContent() );
        $tmpMetadata = stream_get_meta_data( $tmpFile );

        $binaryCreateStruct = $this->ioService->newBinaryCreateStructFromLocalFile( $tmpMetadata['uri'] );
        $binaryCreateStruct->id = $this->getFilePath( $path, $filter );
        $this->ioService->createBinaryFile( $binaryCreateStruct );

        fclose( $tmpFile );
    }

    /**
     * @param string[] $paths The paths where the original files are expected to be.
     * @param string[] $filters The imagine filters in effect.
     *
     * @return void
     */
    public function remove( array $paths, array $filters )
    {
        // TODO: $paths may be empty, meaning that all generated images corresponding to $filters need to be removed.
        if ( empty( $filters ) )
        {
            $filters = array_keys( $this->filterConfiguration->all() );
        }

        foreach ( $paths as $path )
        {
            foreach ( $filters as $filter )
            {
                $filteredImagePath = $this->getFilePath( $path, $filter );
                if ( !$this->ioService->exists( $filteredImagePath ) )
                {
                    continue;
                }

                $binaryFile = $this->ioService->loadBinaryFile( $filteredImagePath );
                $this->ioService->deleteBinaryFile( $binaryFile );
            }
        }
    }

    /**
     * Returns path for filtered image from original path.
     * Pattern is <original_dir>/<filename>_<filter_name>.<extension>
     *
     * e.g. var/ezdemo_site/Tardis/bigger/in-the-inside/RiverSong_thumbnail.jpg
     *
     * @param string $path
     * @param string $filter
     *
     * @return string
     */
    public function getFilePath( $path, $filter )
    {
        $info = pathinfo( $path );
        return sprintf(
            '%s/%s_%s%s',
            $info['dirname'],
            $info['filename'],
            $filter,
            empty( $info['extension'] ) ? '' : '.' . $info['extension']
        );
    }
}
