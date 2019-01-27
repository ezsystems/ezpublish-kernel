<?php

/**
 * File containing the Functional\ViewTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

class ViewTest extends TestCase
{
    /**
     * Covers POST /views.
     */
    public function testViewRequestWithOrStatement()
    {
        $this
            ->createTestContent('View test content foo', md5('View test content foo'))
            ->createTestContent('View test content bar', md5('View test content bar'));

        $request = $this
            ->createHttpRequest('POST', '/api/ezp/v2/views', 'ViewInput+xml', 'View+json');

        $request
            ->setContent('<?xml version="1.0" encoding="UTF-8"?>
                <ViewInput>
                  <identifier>TitleView</identifier>
                  <Query>
                    <Filter>
                        <OR>
                            <ContentRemoteIdCriterion>' . md5('View test content foo') . '</ContentRemoteIdCriterion>
                            <ContentRemoteIdCriterion>' . md5('View test content bar') . '</ContentRemoteIdCriterion>
                        </OR>
                    </Filter>
                    <limit>10</limit>
                    <offset>0</offset>
                  </Query>
                </ViewInput>');

        $response = $this
            ->sendHttpRequest($request);

        $responseData = json_decode($response->getContent(), true);

        self::assertEquals(2, $responseData['View']['Result']['count']);
    }

    /**
     * Covers POST /views.
     *
     * @depends testViewRequestWithOrStatement
     */
    public function testViewRequestWithAndStatement()
    {
        $request = $this
            ->createHttpRequest('POST', '/api/ezp/v2/views', 'ViewInput+xml', 'View+json');

        $request
            ->setContent('<?xml version="1.0" encoding="UTF-8"?>
                <ViewInput>
                  <identifier>TitleView</identifier>
                  <Query>
                    <Filter>
                        <AND>
                            <OR>
                                <ContentRemoteIdCriterion>' . md5('View test content foo') . '</ContentRemoteIdCriterion>
                                <ContentRemoteIdCriterion>' . md5('View test content bar') . '</ContentRemoteIdCriterion>
                            </OR>
                            <ContentRemoteIdCriterion>' . md5('View test content foo') . '</ContentRemoteIdCriterion>
                        </AND>
                    </Filter>
                    <limit>10</limit>
                    <offset>0</offset>
                  </Query>
                </ViewInput>');

        $response = $this
            ->sendHttpRequest($request);

        $responseData = json_decode($response->getContent(), true);

        self::assertEquals(1, $responseData['View']['Result']['count']);
    }

    /**
     * @param string $name
     * @param $remoteId
     * @return self
     */
    protected function createTestContent($name, $remoteId)
    {
        $contentXml = '<?xml version="1.0" encoding="UTF-8"?>
            <ContentCreate>
              <ContentType href="/api/ezp/v2/content/types/1" />
              <mainLanguageCode>eng-GB</mainLanguageCode>
              <LocationCreate>
                <ParentLocation href="/api/ezp/v2/content/locations/1/2" />
                <priority>0</priority>
                <hidden>false</hidden>
                <sortField>PATH</sortField>
                <sortOrder>ASC</sortOrder>
              </LocationCreate>
              <Section href="/api/ezp/v2/content/sections/1" />
              <alwaysAvailable>true</alwaysAvailable>
              <remoteId>' . $remoteId . '</remoteId>
              <User href="/api/ezp/v2/user/users/14" />
              <modificationDate>2012-09-30T12:30:00</modificationDate>
              <fields>
                <field>
                  <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
                  <languageCode>eng-GB</languageCode>
                  <fieldValue>' . $name . '</fieldValue>
                </field>
              </fields>
            </ContentCreate>';

        $this->createContent($contentXml);

        return $this;
    }
}
