<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Search\Legacy\Content\Gateway;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;

class GatewayFactory
{
    /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler */
    private $handler;

    /** @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter */
    private $sortClauseConverter;

    /** @var \eZ\Publish\SPI\Persistence\Content\Language\Handler */
    private $languageHandler;

    /** @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter */
    private $criteriaConverter;

    /** @var iterable */
    private $dbSpecificSortClauseCollection;
    /** @var string */
    private $driverName;

    public function __construct(
        DatabaseHandler $handler,
        CriteriaConverter $criteriaConverter,
        SortClauseConverter $sortClauseConverter,
        LanguageHandler $languageHandler,
        string $driverName,
        iterable $dbSpecificSortClauseCollection
    ) {
        $this->handler = $handler;
        $this->criteriaConverter = $criteriaConverter;
        $this->sortClauseConverter = $sortClauseConverter;
        $this->languageHandler = $languageHandler;
        $this->driverName = $driverName;
        $this->dbSpecificSortClauseCollection = $dbSpecificSortClauseCollection;
    }

    public function getGateway()
    {
//        $this->sortClauseConverter->

        return new DoctrineDatabase(
            $this->handler,
            $this->criteriaConverter,
            $this->sortClauseConverter,
            $this->languageHandler
        );
    }
}
