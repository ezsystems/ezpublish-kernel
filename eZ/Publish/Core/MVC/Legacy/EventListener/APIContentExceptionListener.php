<?php
/**
 * File containing the APIExceptionListener class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\EventListener;

use eZ\Publish\Core\MVC\Symfony\Event\APIContentExceptionEvent,
    eZ\Publish\Core\MVC\Symfony\MVCEvents,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound as ConverterNotFound,
    eZ\Publish\Core\Repository\Values\Content\Location,
    eZ\Publish\Core\Repository\Values\Content\ContentInfo,
    eZ\Publish\Core\MVC\Legacy\View\Provider\Content as LegacyContentViewProvider,
    eZ\Publish\Core\MVC\Legacy\View\Provider\Location as LegacyLocationViewProvider,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Symfony\Component\HttpKernel\Log\LoggerInterface;

class APIContentExceptionListener implements EventSubscriberInterface
{
    /**
     * @var eZ\Publish\Core\MVC\Legacy\View\Provider\Content
     */
    protected $legacyCVP;

    /**
     * @var eZ\Publish\Core\MVC\Legacy\View\Provider\Location
     */
    protected $legacyLVP;

    /**
     * @var \Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    protected $logger;

    public function __construct( LegacyContentViewProvider $legacyCVP, LegacyLocationViewProvider $legacyLVP, LoggerInterface $logger = null )
    {
        $this->legacyCVP = $legacyCVP;
        $this->legacyLVP = $legacyLVP;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MVCEvents::API_CONTENT_EXCEPTION    => 'onAPIContentException'
        );
    }

    public function onAPIContentException( APIContentExceptionEvent $event )
    {
        $exception = $event->getApiException();
        $contentMeta = $event->getContentMeta();
        if ( $exception instanceof ConverterNotFound )
        {
            if ( isset( $this->logger ) )
                $this->logger->notice(
                    'Missing field converter in legacy storage engine, forwarding to legacy kernel.',
                    array( 'content' => $contentMeta )
                );

            if ( isset( $contentMeta['locationId'] ) )
            {
                $event->setContentView(
                    $this->legacyLVP->getView(
                        new Location( array( 'id' => $contentMeta['locationId'] ) ),
                        $contentMeta['viewType']
                    )
                );
            }
            else if ( isset( $contentMeta['contentId'] ) )
            {
                $event->setContentView(
                    $this->legacyCVP->getView(
                        new ContentInfo( array( 'id' => $contentMeta['contentId'] ) ),
                        $contentMeta['viewType']
                    )
                );
            }

            $event->stopPropagation();
        }
    }
}
