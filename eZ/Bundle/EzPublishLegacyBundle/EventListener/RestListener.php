<?php
/**
 * File containing the RestListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use eZ\Bundle\EzPublishRestBundle\RestEvents;
use ezxFormToken;

/**
 * This listener performs Legacy Stack specific tasks in REST context.
 */
class RestListener implements EventSubscriberInterface
{
    /**
     * CSRF token intention string.
     *
     * @var string
     */
    private $csrfTokenIntention;

    /**
     * @param string $csrfTokenIntention
     */
    public function __construct( $csrfTokenIntention )
    {
        $this->csrfTokenIntention = $csrfTokenIntention;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            RestEvents::REST_CSRF_TOKEN_VALIDATED => 'setCsrfIntention'
        );
    }

    /**
     * Injects CSRF token intention to the ezxFormToken extension so that
     * Legacy & Symfony stacks can work together.
     */
    public function setCsrfIntention()
    {
        ezxFormToken::setIntention( $this->csrfTokenIntention );
    }
}
