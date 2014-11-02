<?php
/**
 * File containing the AliasCleaner class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Image;

use eZ\Publish\Core\FieldType\Image\AliasCleanerInterface;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\IO\UrlRedecoratorInterface;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Psr\Log\LoggerInterface;

class AliasCleaner implements AliasCleanerInterface
{
    /**
     * @var AliasCleanerInterface
     */
    private $innerAliasCleaner;

    /**
     * @var IOServiceInterface
     */
    private $ioService;

    /**
     * @var UrlRedecoratorInterface
     */
    private $urlRedecorator;

    public function __construct(
        AliasCleanerInterface $innerAliasCleaner,
        IOServiceInterface $ioService,
        UrlRedecoratorInterface $urlRedecorator,
        LoggerInterface $logger
    )
    {
        $this->innerAliasCleaner = $innerAliasCleaner;
        $this->ioService = $ioService;
        $this->urlRedecorator = $urlRedecorator;
        $this->logger = $logger;
    }

    public function removeAliases( $originalPath )
    {
        try
        {
            $this->innerAliasCleaner->removeAliases(
                $this->ioService->loadBinaryFileByUri(
                    $this->urlRedecorator->redecorateFromTarget( $originalPath )
                )
            );
        }
        catch( NotFoundException $e )
        {
            // May have already been removed by legacy eZImageAliasHandler::removeAliases()
            $logger->debug("Image alias file not found: $originalPath");
        }
    }
}
