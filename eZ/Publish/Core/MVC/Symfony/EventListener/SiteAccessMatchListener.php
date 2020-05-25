<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\EventListener;

// @todo Move SiteAccessMatcherRegistryInterface to eZ\Publish\Core\MVC\Symfony\Matcher
use eZ\Bundle\EzPublishCoreBundle\SiteAccess\SiteAccessMatcherRegistryInterface;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\Component\Serializer\SerializerTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router as SiteAccessRouter;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * kernel.request listener, triggers SiteAccess matching.
 * Should be triggered as early as possible.
 */
class SiteAccessMatchListener implements EventSubscriberInterface
{
    use SerializerTrait;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\Router */
    protected $siteAccessRouter;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var \eZ\Bundle\EzPublishCoreBundle\SiteAccess\SiteAccessMatcherRegistryInterface */
    private $siteAccessMatcherRegistry;

    public function __construct(
        SiteAccessRouter $siteAccessRouter,
        EventDispatcherInterface $eventDispatcher,
        SiteAccessMatcherRegistryInterface $siteAccessMatcherRegistry
    ) {
        $this->siteAccessRouter = $siteAccessRouter;
        $this->eventDispatcher = $eventDispatcher;
        $this->siteAccessMatcherRegistry = $siteAccessMatcherRegistry;
    }

    public static function getSubscribedEvents()
    {
        return [
            // Should take place just after FragmentListener (priority 48) in order to get rebuilt request attributes in case of subrequest
            KernelEvents::REQUEST => ['onKernelRequest', 45],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // We have a serialized siteaccess object from a fragment (sub-request), we need to get it back.
        if ($request->attributes->has('serialized_siteaccess')) {
            $serializer = $this->getSerializer();
            /** @var SiteAccess $siteAccess */
            $siteAccess = $serializer->deserialize($request->attributes->get('serialized_siteaccess'), SiteAccess::class, 'json');
            if ($siteAccess->matcher !== null) {
                $siteAccess->matcher = $this->deserializeMatcher(
                    $serializer,
                    $siteAccess->matcher,
                    $request->attributes->get('serialized_siteaccess_matcher'),
                    $request->attributes->get('serialized_siteaccess_sub_matchers')
                );
            }

            $request->attributes->set(
                'siteaccess',
                $siteAccess
            );
            $request->attributes->remove('serialized_siteaccess');
        } elseif (!$request->attributes->has('siteaccess')) {
            // Get SiteAccess from original request if present ("_ez_original_request" attribute), or current request otherwise.
            // "_ez_original_request" attribute is present in the case of user context hash generation (aka "user hash request").
            $request->attributes->set(
                'siteaccess',
                $this->getSiteAccessFromRequest($request->attributes->get('_ez_original_request', $request))
            );
        }

        $siteaccess = $request->attributes->get('siteaccess');
        if ($siteaccess instanceof SiteAccess) {
            $siteAccessEvent = new PostSiteAccessMatchEvent($siteaccess, $request, $event->getRequestType());
            $this->eventDispatcher->dispatch($siteAccessEvent, MVCEvents::SITEACCESS);
        }
    }

    /**
     * @param Request $request
     *
     * @return SiteAccess
     */
    private function getSiteAccessFromRequest(Request $request)
    {
        return $this->siteAccessRouter->match(
            new SimplifiedRequest(
                [
                    'scheme' => $request->getScheme(),
                    'host' => $request->getHost(),
                    'port' => $request->getPort(),
                    'pathinfo' => $request->getPathInfo(),
                    'queryParams' => $request->query->all(),
                    'languages' => $request->getLanguages(),
                    'headers' => $request->headers->all(),
                ]
            )
        );
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    private function deserializeMatcher(
        SerializerInterface $serializer,
        string $matcherFQCN,
        string $serializedMatcher,
        ?array $serializedSubMatchers
    ): SiteAccess\Matcher {
        $matcher = null;
        if (in_array(SiteAccess\Matcher::class, class_implements($matcherFQCN), true)) {
            $matcher = $this->buildMatcherFromSerializedClass(
                $serializer,
                $matcherFQCN,
                $serializedMatcher
            );
        } else {
            throw new InvalidArgumentException(
                'matcher',
                sprintf(
                    'SiteAccess matcher must implement %s or %s',
                    SiteAccess\Matcher::class,
                    SiteAccess\URILexer::class
                )
            );
        }
        if (!empty($serializedSubMatchers)) {
            $subMatchers = [];
            foreach ($serializedSubMatchers as $subMatcherFQCN => $serializedData) {
                $subMatchers[$subMatcherFQCN] = $this->buildMatcherFromSerializedClass(
                    $serializer,
                    $subMatcherFQCN,
                    $serializedData
                );
            }
            $matcher->setSubMatchers($subMatchers);
        }

        return $matcher;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function buildMatcherFromSerializedClass(
        SerializerInterface $serializer,
        string $matcherClass,
        string $serializedMatcher
    ): SiteAccess\Matcher {
        $matcher = null;
        if ($this->siteAccessMatcherRegistry->hasMatcher($matcherClass)) {
            $matcher = $this->siteAccessMatcherRegistry->getMatcher($matcherClass);
        } else {
            $matcher = $serializer->deserialize(
                $serializedMatcher,
                $matcherClass,
                'json'
            );
        }

        return $matcher;
    }
}
