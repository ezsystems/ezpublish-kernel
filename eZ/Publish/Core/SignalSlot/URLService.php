<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\URLService as URLServiceInterface;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;
use eZ\Publish\Core\SignalSlot\Signal\URLService\UpdateUrlSignal;

class URLService implements URLServiceInterface
{
    /**
     * Aggregated service.
     *
     * @var \eZ\Publish\API\Repository\URLService
     */
    protected $service;

    /**
     * SignalDispatcher.
     *
     * @var SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * URLService constructor.
     *
     * @param \eZ\Publish\API\Repository\URLService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(URLServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        $this->service = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function createUpdateStruct()
    {
        return $this->service->createUpdateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function findUrls(URLQuery $query)
    {
        return $this->service->findUrls($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findUsages(URL $url, $offset = 0, $limit = -1)
    {
        return $this->service->findUsages($url, $offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function loadById($id)
    {
        return $this->service->loadById($id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadByUrl($url)
    {
        return $this->service->loadByUrl($url);
    }

    /**
     * {@inheritdoc}
     */
    public function updateUrl(URL $url, URLUpdateStruct $struct)
    {
        $returnValue = $this->service->updateUrl($url, $struct);

        $this->signalDispatcher->emit(
            new UpdateUrlSignal([
                'urlId' => $returnValue->id,
                'urlChanged' => $struct->url !== null,
            ])
        );

        return $returnValue;
    }
}
