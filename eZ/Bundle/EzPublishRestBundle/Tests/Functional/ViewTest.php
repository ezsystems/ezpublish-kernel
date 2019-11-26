<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use stdClass;

class ViewTest extends TestCase
{
    /** @var string[] */
    private static $createdContentRemoteIds = [];

    /**
     * Covers POST /views.
     *
     * @dataProvider providerForTestViewRequest
     *
     * @param \stdClass[] $contentDataList list of items containing name and remoteId properties
     * @throws \Psr\Http\Client\ClientException
     */
    public function testViewRequest(
        string $body,
        string $format,
        int $expectedResultsCount,
        array $contentDataList
    ): void {
        $this->createTestContentItems($contentDataList);

        // search for Content
        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/views',
            "ViewInput+{$format}",
            'View+json',
            $body
        );
        $response = $this->sendHttpRequest($request);
        $responseData = json_decode($response->getBody()->getContents(), true);

        if (isset($responseData['ErrorMessage'])) {
            self::fail(var_export($responseData, true));
        }

        self::assertEquals($expectedResultsCount, $responseData['View']['Result']['count']);
    }

    /**
     * Data provider for testViewRequestWithOrStatement.
     *
     * @return array
     */
    public function providerForTestViewRequest(): array
    {
        $foo = new stdClass();
        $foo->name = uniqid('View test content foo');
        $foo->remoteId = md5($foo->name);

        $bar = new stdClass();
        $bar->name = uniqid('View test content bar');
        $bar->remoteId = md5($bar->name);

        return [
            [
                <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
  <identifier>TitleView</identifier>
  <Query>
    <Filter>
        <OR>
            <ContentRemoteIdCriterion>{$foo->remoteId}</ContentRemoteIdCriterion>
            <ContentRemoteIdCriterion>{$bar->remoteId}</ContentRemoteIdCriterion>
        </OR>
    </Filter>
    <limit>10</limit>
    <offset>0</offset>
  </Query>
</ViewInput>
XML,
                'xml',
                2,
                [$foo, $bar],
            ],
            [
                <<< JSON
{
  "ViewInput": {
    "identifier": "TitleView",
    "Query": {
      "Filter": {
        "OR": {
          "ContentRemoteIdCriterion": [
            "{$foo->remoteId}",
            "{$bar->remoteId}"
          ]
        }
      },
      "limit": "10",
      "offset": "0"
    }
  }
}
JSON,
                'json',
                2,
                [$foo, $bar],
            ],
            [
                <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
  <identifier>TitleView</identifier>
  <Query>
    <Filter>
        <AND>
            <OR>
                <ContentRemoteIdCriterion>{$foo->remoteId}</ContentRemoteIdCriterion>
                <ContentRemoteIdCriterion>{$bar->remoteId}</ContentRemoteIdCriterion>
            </OR>
            <ContentRemoteIdCriterion>{$foo->remoteId}</ContentRemoteIdCriterion>
        </AND>
    </Filter>
    <limit>10</limit>
    <offset>0</offset>
  </Query>
</ViewInput>
XML,
                'xml',
                1,
                [$foo, $bar],
            ],
            [
                <<< JSON
{
  "ViewInput": {
    "identifier": "TitleView",
    "public": true,
    "Query": {
      "Filter": {
        "OR": [
          {
            "ContentRemoteIdCriterion": "{$foo->remoteId}"
          },
          {
            "ContentRemoteIdCriterion": "{$bar->remoteId}"
          }
        ]
      },
      "FacetBuilders": {},
      "SortClauses": {},
      "limit": 1000,
      "offset": 0
    }
  }
}
JSON,
                'json',
                2,
                [$foo, $bar],
            ],
            [
                <<< JSON
{
  "ViewInput": {
    "identifier": "udw-locations-by-parent-location-id-1",
    "public": false,
    "Query": {
      "Criteria": {},
      "FacetBuilders": {},
      "SortClauses": {},
      "Filter": {
        "AND": [
          {
            "OR": [
              {
                "ContentTypeIdentifierCriterion": "folder"
              },
              {
                "ContentTypeIdentifierCriterion": "article"
              }
            ]
          },
          {
            "OR": [
              {
                "ContentRemoteIdCriterion": "{$foo->remoteId}"
              },
              {
                "ContentRemoteIdCriterion": "{$bar->remoteId}"
              }
            ]
          }
        ]
      },
      "limit": 50,
      "offset": 0
    }
  }
}
JSON,
                'json',
                2,
                [$foo, $bar],
            ],
        ];
    }

    /**
     * @param \stdClass[] $contentDataList
     */
    private function createTestContentItems(array $contentDataList): void
    {
        foreach ($contentDataList as $contentData) {
            // skip creating already created items
            if (in_array($contentData->remoteId, self::$createdContentRemoteIds)) {
                continue;
            }

            $this->createFolder(
                $contentData->name,
                '/api/ezp/v2/content/locations/1/2',
                $contentData->remoteId
            );
            self::$createdContentRemoteIds[] = $contentData->remoteId;
        }
    }
}
