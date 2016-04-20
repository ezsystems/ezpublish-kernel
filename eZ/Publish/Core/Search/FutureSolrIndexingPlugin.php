<?php

namespace eZ\Publish\Core\Search;

use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\API\Repository\Values\Content\LocationFilter;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;

class FutureSolrIndexingPlugin
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    public function __construct(LocationHandler $locationHandler)
    {
        $this->locationHandler = $locationHandler;
    }

    public function canMap(Content $content, $languageCode)
    {
        if (
            $content->versionInfo->contentInfo->contentTypeId === 42 &&
            $languageCode === 'cro-HR'
        ) {
            return true;
        }

        return false;
    }

    public function mapFields(Content $content, $languageCode)
    {
        $filter = new LocationFilter();

        // Something...

        $filterResult = $this->locationHandler->filter($filter);

        $fields = [];

        $fields[] = new Field(
            'hello_field',
            $filterResult->searchHits[0]->valueObject->hello,
            new FieldType\StringField()
        );

        return $fields;
    }
}
