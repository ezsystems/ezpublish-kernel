<?php

/**
 * File containing the IOCacheResolver class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\IO\Values\MissingBinaryFile;
use eZ\Publish\SPI\Variation\VariationPurger;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\Routing\RequestContext;

/**
 * LiipImagineBundle cache resolver using eZ IO repository.
 */
class IORepositoryResolver implements ResolverInterface
{
    const VARIATION_ORIGINAL = 'original';

    /** @var \eZ\Publish\Core\IO\IOServiceInterface */
    private $ioService;

    /** @var \Symfony\Component\Routing\RequestContext */
    private $requestContext;

    /** @var FilterConfiguration */
    private $filterConfiguration;
    /** @var \eZ\Publish\SPI\Variation\VariationPurger */
    private $variationPurger;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator */
    private $variationPathGenerator;

    public function __construct(
        IOServiceInterface $ioService,
        RequestContext $requestContext,
        FilterConfiguration $filterConfiguration,
        VariationPurger $variationPurger,
        VariationPathGenerator $variationPathGenerator
    ) {
        $this->ioService = $ioService;
        $this->requestContext = $requestContext;
        $this->filterConfiguration = $filterConfiguration;
        $this->variationPurger = $variationPurger;
        $this->variationPathGenerator = $variationPathGenerator;
    }

    public function isStored($path, $filter)
    {
        return $this->ioService->exists($this->getFilePath($path, $filter));
    }

    public function resolve($path, $filter)
    {
        try {
            $binaryFile = $this->ioService->loadBinaryFile($path);

            // Treat a MissingBinaryFile as a not loadable file.
            if ($binaryFile instanceof MissingBinaryFile) {
                throw new NotResolvableException("Variation image not found in $path");
            }

            if ($filter !== static::VARIATION_ORIGINAL) {
                $variationPath = $this->getFilePath($path, $filter);
                $variationBinaryFile = $this->ioService->loadBinaryFile($variationPath);
                $path = $variationBinaryFile->uri;
            } else {
                $path = $binaryFile->uri;
            }

            return sprintf(
                '%s%s',
                $path[0] === '/' ? $this->getBaseUrl() : '',
                $path
            );
        } catch (NotFoundException $e) {
            throw new NotResolvableException("Variation image not found in $path", 0, $e);
        }
    }

    /**
     * Stores image alias in the IO Repository.
     * A temporary file is created to dump the filtered image and is used as basis for creation in the IO Repository.
     *
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        $tmpFile = tmpfile();
        fwrite($tmpFile, $binary->getContent());
        $tmpMetadata = stream_get_meta_data($tmpFile);

        $binaryCreateStruct = $this->ioService->newBinaryCreateStructFromLocalFile($tmpMetadata['uri']);
        $binaryCreateStruct->id = $this->getFilePath($path, $filter);
        $this->ioService->createBinaryFile($binaryCreateStruct);

        fclose($tmpFile);
    }

    /**
     * @param string[] $paths The paths where the original files are expected to be.
     * @param string[] $filters The imagine filters in effect.
     */
    public function remove(array $paths, array $filters)
    {
        if (empty($filters)) {
            $filters = array_keys($this->filterConfiguration->all());
        }

        if (empty($paths)) {
            $this->variationPurger->purge($filters);
        }

        foreach ($paths as $path) {
            foreach ($filters as $filter) {
                $filteredImagePath = $this->getFilePath($path, $filter);
                if (!$this->ioService->exists($filteredImagePath)) {
                    continue;
                }

                $binaryFile = $this->ioService->loadBinaryFile($filteredImagePath);
                $this->ioService->deleteBinaryFile($binaryFile);
            }
        }
    }

    /**
     * Returns path for filtered image from original path, using the VariationPathGenerator.
     *
     * @param string $path
     * @param string $filter
     *
     * @return string
     */
    public function getFilePath($path, $filter)
    {
        return $this->variationPathGenerator->getVariationPath($path, $filter);
    }

    /**
     * Returns base URL, with scheme, host and port, for current request context.
     * If no delivery URL is configured for current SiteAccess, will return base URL from current RequestContext.
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        $port = '';
        if ($this->requestContext->getScheme() === 'https' && $this->requestContext->getHttpsPort() != 443) {
            $port = ":{$this->requestContext->getHttpsPort()}";
        }

        if ($this->requestContext->getScheme() === 'http' && $this->requestContext->getHttpPort() != 80) {
            $port = ":{$this->requestContext->getHttpPort()}";
        }

        $baseUrl = $this->requestContext->getBaseUrl();
        if (substr($this->requestContext->getBaseUrl(), -4) === '.php') {
            $baseUrl = pathinfo($this->requestContext->getBaseurl(), PATHINFO_DIRNAME);
        }
        $baseUrl = rtrim($baseUrl, '/\\');

        return sprintf(
            '%s://%s%s%s',
            $this->requestContext->getScheme(),
            $this->requestContext->getHost(),
            $port,
            $baseUrl
        );
    }
}
