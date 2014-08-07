<?php
/**
 * File containing the IOCacheResolver class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

use eZ\Publish\Core\IO\IOServiceInterface;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * LiipImagineBundle cache resolver using eZ IO repository.
 */
class IORepositoryResolver implements ResolverInterface
{
    /**
     * @var \eZ\Publish\Core\IO\IOServiceInterface
     */
    private $ioService;

    /**
     * @var \Symfony\Component\Routing\RequestContext
     */
    private $requestContext;

    public function __construct( IOServiceInterface $ioService, RequestContext $requestContext )
    {
        $this->ioService = $ioService;
        $this->requestContext = $requestContext;
    }

    public function isStored( $path, $filter )
    {
        return $this->ioService->exists( $this->getFilePath( $path, $filter ) );
    }

    public function resolve( $path, $filter )
    {
        return sprintf( '%s/%s', $this->getBaseUrl(), $this->getFilePath( $path, $filter ) );
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
            '%s/%s_%s.%s',
            $info['dirname'],
            $info['filename'],
            $filter,
            $info['extension'] ?: ''
        );
    }

    /**
     * Returns base URL, with scheme, host and port, for current request context.
     *
     * @todo Allow to use a custom domain.
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        $port = '';
        if ( $this->requestContext->getScheme() === 'https' && $this->requestContext->getHttpsPort() != 443 )
        {
            $port = ":{$this->requestContext->getHttpsPort()}";
        }

        if ( $this->requestContext->getScheme() === 'http' && $this->requestContext->getHttpPort() != 80 )
        {
            $port = ":{$this->requestContext->getHttpPort()}";
        }

        $baseUrl = $this->requestContext->getBaseUrl();
        if ( substr( $this->requestContext->getBaseUrl(), -4 ) === '.php' )
        {
            $baseUrl = pathinfo( $this->requestContext->getBaseurl(), PATHINFO_DIRNAME );
        }
        $baseUrl = rtrim( $baseUrl, '/\\' );

        return sprintf(
            '%s://%s%s%s',
            $this->requestContext->getScheme(),
            $this->requestContext->getHost(),
            $port,
            $baseUrl
        );
    }
}
