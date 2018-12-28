<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\URLService as URLServiceInterface;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;
use eZ\Publish\Core\Repository\Decorator\URLServiceDecorator;
use eZ\Publish\Core\SignalSlot\Signal\URLService\UpdateUrlSignal;

class URLService extends URLServiceDecorator
{
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
        parent::__construct($service);

        $this->signalDispatcher = $signalDispatcher;
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
