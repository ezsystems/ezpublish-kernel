<?php

/**
 * File containing the URLServiceServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\URL\Query\Criterion as Criterion;
use eZ\Publish\API\Repository\Values\URL\Query\SortClause as SortClause;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;
use DateTime;
use eZ\Publish\API\Repository\Values\URL\UsageSearchResult;

/**
 * Test case for operations in the UserService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\URLService
 * @group integration
 * @group url
 */
class URLServiceTest extends BaseURLServiceTest
{
    /**
     * Test for URLService::findUrls() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::findUrls()
     */
    public function testFindUrls()
    {
        $expectedUrls = [
            'http://www.apache.org/',
            'http://calendar.google.com/calendar/render',
            'http://www.dropbox.com/',
            '/content/view/sitemap/2',
            'http://support.google.com/chrome/answer/95647?hl=es',
            'http://www.nazwa.pl/',
            'http://www.facebook.com/sharer.php',
            'http://www.wikipedia.org/',
            'http://www.google.de/',
            'http://www.google.com/',
            'http://www.nginx.com/',
            '/content/view/tagcloud/2',
            'http://www.youtube.com/',
            'http://vimeo.com/',
            'http://windows.microsoft.com/en-US/internet-explorer/products/ie/home',
            'http://twitter.com/',
            'http://www.google.com/analytics/',
            'http://www.facebook.com/',
            'http://www.discuz.net/forum.php',
            'http://instagram.com/',
        ];

        $query = new URLQuery();
        $query->filter = new Criterion\MatchAll();

        $this->doTestFindUrls($query, $expectedUrls);
    }

    /**
     * Test for URLService::findUrls() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::findUrls()
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testFindUrls
     */
    public function testFindUrlsUsingMatchNone()
    {
        $query = new URLQuery();
        $query->filter = new Criterion\MatchNone();

        $this->doTestFindUrls($query, []);
    }

    /**
     * Test for URLService::findUrls() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::findUrls()
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testFindUrls
     */
    public function testFindUrlsUsingPatternCriterion()
    {
        $expectedUrls = [
            'http://www.google.de/',
            'http://www.google.com/',
            'http://support.google.com/chrome/answer/95647?hl=es',
            'http://calendar.google.com/calendar/render',
            'http://www.google.com/analytics/',
        ];

        $query = new URLQuery();
        $query->filter = new Criterion\Pattern('google');

        $this->doTestFindUrls($query, $expectedUrls);
    }

    /**
     * Test for URLService::findUrls() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::findUrls()
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testFindUrls
     */
    public function testFindUrlsUsingValidityCriterionValid()
    {
        $expectedUrls = [
            'http://www.google.com/',
            '/content/view/sitemap/2',
            'http://support.google.com/chrome/answer/95647?hl=es',
            'http://www.google.de/',
            'http://www.nginx.com/',
            'http://www.google.com/analytics/',
            'http://www.discuz.net/forum.php',
            'http://www.wikipedia.org/',
            'http://www.facebook.com/sharer.php',
            'http://twitter.com/',
            'http://www.nazwa.pl/',
            'http://instagram.com/',
            'http://www.apache.org/',
            'http://www.dropbox.com/',
            'http://www.facebook.com/',
            '/content/view/tagcloud/2',
            'http://www.youtube.com/',
        ];

        $query = new URLQuery();
        $query->filter = new Criterion\Validity(true);

        $this->doTestFindUrls($query, $expectedUrls);
    }

    /**
     * Test for URLService::findUrls() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::findUrls()
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testFindUrls
     */
    public function testFindUrlsUsingValidityCriterionInvalid()
    {
        $expectedUrls = [
            'http://calendar.google.com/calendar/render',
            'http://vimeo.com/',
            'http://windows.microsoft.com/en-US/internet-explorer/products/ie/home',
        ];

        $query = new URLQuery();
        $query->filter = new Criterion\Validity(false);

        $this->doTestFindUrls($query, $expectedUrls);
    }

    /**
     * Test for URLService::findUrls() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::findUrls()
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testFindUrls
     */
    public function testFindUrlsUsingVisibleOnlyCriterion()
    {
        $expectedUrls = [
            '/content/view/sitemap/2',
            '/content/view/tagcloud/2',
        ];

        $query = new URLQuery();
        $query->filter = new Criterion\VisibleOnly();

        $this->doTestFindUrls($query, $expectedUrls);
    }

    /**
     * Test for URLService::findUrls() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::findUrls()
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     */
    public function testFindUrlsWithInvalidOffsetThrowsInvalidArgumentException()
    {
        $query = new URLQuery();
        $query->filter = new Criterion\MatchAll();
        $query->offset = 'invalid!';

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlService = $repository->getURLService();
        // This call will fail with a InvalidArgumentException
        $urlService->findUrls($query);
        /* END: Use Case */
    }

    /**
     * Test for URLService::findUrls() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::findUrls()
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     */
    public function testFindUrlsWithInvalidLimitThrowsInvalidArgumentException()
    {
        $query = new URLQuery();
        $query->filter = new Criterion\MatchAll();
        $query->limit = 'invalid!';

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlService = $repository->getURLService();
        // This call will fail with a InvalidArgumentException
        $urlService->findUrls($query);
        /* END: Use Case */
    }

    /**
     * Test for URLService::findUrls() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::findUrls()
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testFindUrls
     */
    public function testFindUrlsWithOffset()
    {
        $expectedUrls = [
            'http://www.discuz.net/forum.php',
            'http://calendar.google.com/calendar/render',
            'http://www.wikipedia.org/',
            'http://www.google.com/analytics/',
            'http://www.nazwa.pl/',
            'http://www.apache.org/',
            'http://www.nginx.com/',
            'http://windows.microsoft.com/en-US/internet-explorer/products/ie/home',
            'http://www.dropbox.com/',
            'http://www.google.de/',
        ];

        $query = new URLQuery();
        $query->filter = new Criterion\MatchAll();
        $query->offset = 10;

        $this->doTestFindUrls($query, $expectedUrls, 20);
    }

    /**
     * Test for URLService::findUrls() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::findUrls()
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testFindUrls
     */
    public function testFindUrlsWithOffsetAndLimit()
    {
        $expectedUrls = [
            'http://www.discuz.net/forum.php',
            'http://calendar.google.com/calendar/render',
            'http://www.wikipedia.org/',
        ];

        $query = new URLQuery();
        $query->filter = new Criterion\MatchAll();
        $query->offset = 10;
        $query->limit = 3;

        $this->doTestFindUrls($query, $expectedUrls, 20);
    }

    /**
     * Test for URLService::findUrls() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::findUrls()
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testFindUrls
     */
    public function testFindUrlsWithLimitZero()
    {
        $query = new URLQuery();
        $query->filter = new Criterion\MatchAll();
        $query->limit = 0;

        $this->doTestFindUrls($query, [], 20);
    }

    /**
     * Test for URLService::findUrls() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::findUrls()
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testFindUrls
     * @dataProvider dataProviderForFindUrlsWithSorting
     */
    public function testFindUrlsWithSorting(SortClause $sortClause, array $expectedUrls)
    {
        $query = new URLQuery();
        $query->filter = new Criterion\MatchAll();
        $query->sortClauses = [$sortClause];

        $this->doTestFindUrls($query, $expectedUrls, null, false);
    }

    public function dataProviderForFindUrlsWithSorting()
    {
        $urlsSortedById = [
            '/content/view/sitemap/2',
            '/content/view/tagcloud/2',
            'http://twitter.com/',
            'http://www.facebook.com/',
            'http://www.google.com/',
            'http://vimeo.com/',
            'http://www.facebook.com/sharer.php',
            'http://www.youtube.com/',
            'http://support.google.com/chrome/answer/95647?hl=es',
            'http://instagram.com/',
            'http://www.discuz.net/forum.php',
            'http://calendar.google.com/calendar/render',
            'http://www.wikipedia.org/',
            'http://www.google.com/analytics/',
            'http://www.nazwa.pl/',
            'http://www.apache.org/',
            'http://www.nginx.com/',
            'http://windows.microsoft.com/en-US/internet-explorer/products/ie/home',
            'http://www.dropbox.com/',
            'http://www.google.de/',
        ];

        $urlsSortedByURL = $urlsSortedById;
        sort($urlsSortedByURL);

        return [
            [new SortClause\Id(SortClause::SORT_ASC), $urlsSortedById],
            [new SortClause\Id(SortClause::SORT_DESC), array_reverse($urlsSortedById)],
            [new SortClause\URL(SortClause::SORT_ASC), $urlsSortedByURL],
            [new SortClause\URL(SortClause::SORT_DESC), array_reverse($urlsSortedByURL)],
        ];
    }

    /**
     * Test for URLService::updateUrl() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::updateUrl()
     */
    public function testUpdateUrl()
    {
        $repository = $this->getRepository();

        $id = $this->generateId('url', 28);

        /* BEGIN: Use Case */
        $urlService = $repository->getURLService();

        $urlBeforeUpdate = $urlService->loadById($id);
        $updateStruct = $urlService->createUpdateStruct();
        $updateStruct->url = 'https://vimeo.com/';

        $urlAfterUpdate = $urlService->updateUrl($urlBeforeUpdate, $updateStruct);
        /* END: Use Case */

        $this->assertInstanceOf(URL::class, $urlAfterUpdate);
        $this->assertPropertiesCorrect([
            'id' => 28,
            'url' => 'https://vimeo.com/',
            // (!) URL status should be reset to valid nad never checked
            'isValid' => true,
            'lastChecked' => null,
            'created' => new DateTime('@1512230697'),
        ], $urlAfterUpdate);
        $this->assertGreaterThanOrEqual($urlBeforeUpdate->modified, $urlAfterUpdate->modified);
    }

    /**
     * Test for URLService::updateUrl() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::updateUrl()
     */
    public function testUpdateUrlStatus()
    {
        $repository = $this->getRepository();

        $id = $this->generateId('url', 26);
        $checked = new DateTime('@' . time());

        /* BEGIN: Use Case */
        $urlService = $repository->getURLService();

        $urlBeforeUpdate = $urlService->loadById($id);
        $updateStruct = $urlService->createUpdateStruct();
        $updateStruct->isValid = false;
        $updateStruct->lastChecked = $checked;

        $urlAfterUpdate = $urlService->updateUrl($urlBeforeUpdate, $updateStruct);
        /* END: Use Case */

        $this->assertInstanceOf(URL::class, $urlAfterUpdate);
        $this->assertPropertiesCorrect([
            'id' => 26,
            'url' => 'http://www.facebook.com/',
            // (!) URL status should be reset to valid nad never checked
            'isValid' => false,
            'lastChecked' => $checked,
            'created' => new DateTime('@1512230697'),
        ], $urlAfterUpdate);
        $this->assertGreaterThanOrEqual($urlBeforeUpdate->modified, $urlAfterUpdate->modified);
    }

    /**
     * Test for URLService::updateUrl() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::updateUrl()
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testUpdateUrl
     */
    public function testUpdateUrlWithNonUniqueUrl()
    {
        $repository = $this->getRepository();

        $id = $this->generateId('url', 28);

        /* BEGIN: Use Case */
        $urlService = $repository->getURLService();

        $urlBeforeUpdate = $urlService->loadById($id);
        $updateStruct = $urlService->createUpdateStruct();
        $updateStruct->url = 'http://www.youtube.com/';

        // This call will fail with a InvalidArgumentException
        $urlService->updateUrl($urlBeforeUpdate, $updateStruct);
        /* END: Use Case */
    }

    /**
     * Test for URLService::loadById() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::loadById
     */
    public function testLoadById()
    {
        $repository = $this->getRepository();

        $id = $this->generateId('url', 23);

        /* BEGIN: Use Case */
        $urlService = $repository->getURLService();

        $url = $urlService->loadById($id);
        /* END: Use Case */

        $this->assertInstanceOf(URL::class, $url);
        $this->assertPropertiesCorrect([
            'id' => 23,
            'url' => '/content/view/sitemap/2',
            'isValid' => true,
            'lastChecked' => null,
            'created' => new DateTime('@1343140541'),
            'modified' => new DateTime('@1343140541'),
        ], $url);
    }

    /**
     * Test for URLService::loadById() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::loadById
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testLoadById
     */
    public function testLoadByIdThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistentUrlId = $this->generateId('url', self::DB_INT_MAX);
        /* BEGIN: Use Case */
        $urlService = $repository->getURLService();

        // This call will fail with a NotFoundException
        $url = $urlService->loadById($nonExistentUrlId);
        /* END: Use Case */
    }

    /**
     * Test for URLService::loadByUrl() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::loadByUrl
     */
    public function testLoadByUrl()
    {
        $repository = $this->getRepository();

        $urlAddr = '/content/view/sitemap/2';
        /* BEGIN: Use Case */
        $urlService = $repository->getURLService();

        $url = $urlService->loadByUrl($urlAddr);

        /* END: Use Case */

        $this->assertInstanceOf(URL::class, $url);
        $this->assertPropertiesCorrect([
            'id' => 23,
            'url' => '/content/view/sitemap/2',
            'isValid' => true,
            'lastChecked' => null,
            'created' => new DateTime('@1343140541'),
            'modified' => new DateTime('@1343140541'),
        ], $url);
    }

    /**
     * Test for URLService::loadByUrl() method.
     *
     * @see \eZ\Publish\Core\Repository\URLService::loadByUrl
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testLoadByUrl
     */
    public function testLoadByUrlThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistentUrl = 'https://laravel.com/';
        /* BEGIN: Use Case */
        $urlService = $repository->getURLService();

        // This call will fail with a NotFoundException
        $url = $urlService->loadByUrl($nonExistentUrl);
        /* END: Use Case */
    }

    /**
     * Test for URLService::createUpdateStruct() method.
     *
     * @see \eZ\Publish\API\Repository\URLService::createUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\URL\URLUpdateStruct
     */
    public function testCreateUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlService = $repository->getURLService();
        $updateStruct = $urlService->createUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(URLUpdateStruct::class, $updateStruct);

        return $updateStruct;
    }

    /**
     * Test for URLService::createUpdateStruct() method.
     *
     * @param \eZ\Publish\API\Repository\Values\URL\URLUpdateStruct $updateStruct
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testCreateUpdateStruct
     */
    public function testCreateUpdateStructValues(URLUpdateStruct $updateStruct)
    {
        $this->assertPropertiesCorrect([
            'url' => null,
            'isValid' => null,
            'lastChecked' => null,
        ], $updateStruct);
    }

    /**
     * Test for URLService::testFindUsages() method.
     *
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testLoadById
     * @dataProvider dataProviderForFindUsages
     */
    public function testFindUsages($urlId, $offset, $limit, array $expectedContentInfos, $expectedTotalCount = null)
    {
        $repository = $this->getRepository();

        $id = $this->generateId('url', $urlId);
        /* BEGIN: Use Case */
        $urlService = $repository->getURLService();

        $loadedUrl = $urlService->loadById($id);

        $usagesSearchResults = $urlService->findUsages($loadedUrl, $offset, $limit);
        /* END: Use Case */

        $this->assertInstanceOf(UsageSearchResult::class, $usagesSearchResults);
        $this->assertEquals($expectedTotalCount, $usagesSearchResults->totalCount);
        $this->assertUsagesSearchResultItems($usagesSearchResults, $expectedContentInfos);
    }

    public function dataProviderForFindUsages()
    {
        return [
            // findUsages($url, 0, -1)
            [23, 0, -1, [54], 1],
            // findUsages($url, 0, $limit)
            [23, 0, 1, [54], 1],
        ];
    }

    /**
     * Test for URLService::testFindUsages() method.
     *
     * @depends eZ\Publish\API\Repository\Tests\URLServiceTest::testFindUsages
     */
    public function testFindUsagesReturnsEmptySearchResults()
    {
        $repository = $this->getRepository();

        $urlIdUsedOnlyInDraft = $this->generateId('url', 36);
        /* BEGIN: Use Case */
        $urlService = $repository->getURLService();

        $loadedUrl = $urlService->loadById($urlIdUsedOnlyInDraft);

        $usagesSearchResults = $urlService->findUsages($loadedUrl);
        /* END: Use Case */

        $this->assertInstanceOf(UsageSearchResult::class, $usagesSearchResults);
        $this->assertPropertiesCorrect([
            'totalCount' => 0,
            'items' => [],
        ], $usagesSearchResults);
    }
}
