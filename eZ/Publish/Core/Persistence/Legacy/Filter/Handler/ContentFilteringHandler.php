<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\Handler;

use eZ\Publish\API\Repository\Values\Filter\Filter;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler;
use eZ\Publish\Core\Persistence\Legacy\Filter\Gateway\Gateway as FilteringGateway;
use eZ\Publish\Core\Persistence\Legacy\Filter\Gateway\Content\GatewayDataMapper;
use eZ\Publish\SPI\Persistence\Content\ContentItem;
use eZ\Publish\SPI\Persistence\Filter\Content\Handler;
use eZ\Publish\SPI\Persistence\Filter\Content\LazyContentItemListIterator;

/**
 * @internal for internal use by Repository Storage abstraction
 */
final class ContentFilteringHandler implements Handler
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Filter\Gateway\Gateway */
    private $gateway;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Filter\Gateway\Content\GatewayDataMapper */
    private $mapper;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler */
    private $fieldHandler;

    public function __construct(
        FilteringGateway $gateway,
        GatewayDataMapper $mapper,
        FieldHandler $fieldHandler
    ) {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
        $this->fieldHandler = $fieldHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Filter\Content\LazyContentItemListIterator
     */
    public function find(Filter $filter): iterable
    {
        $count = $this->gateway->count($filter->getCriterion());

        // wrapped list before creating the actual API ContentList to pass totalCount
        // for paginated result a total count is not going to be a number of items in a current page
        $list = new LazyContentItemListIterator($count);
        if ($count === 0) {
            return $list;
        }

        $list->prepareIterator(
            $this->gateway->find(
                $filter->getCriterion(),
                $filter->getSortClauses(),
                $filter->getLimit(),
                $filter->getOffset()
            ),
            // called on each iteration of the  iterator returned by find
            function (array $row): ContentItem {
                $contentItem = $this->mapper->mapRawDataToPersistenceContentItem($row);
                $this->fieldHandler->loadExternalFieldData($contentItem->getContent());

                return $contentItem;
            }
        );

        return $list;
    }
}
