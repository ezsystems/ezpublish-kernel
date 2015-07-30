<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlAlias\Gateway\DoctrineDatabaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlAlias\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler as LanguageHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Mapper as LanguageMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\DoctrineDatabase as LanguageGateway;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase.
 *
 * @group urlalias-gateway
 */
class DoctrineDatabaseTest extends TestCase
{
    /**
     * Database gateway to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway
     */
    protected $gateway;

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::__construct
     */
    public function testConstructor()
    {
        $dbHandler = $this->getDatabaseHandler();
        $gateway = $this->getGateway();

        $this->assertAttributeSame(
            $dbHandler,
            'dbHandler',
            $gateway
        );
    }

    /**
     * Test for the loadUrlAliasData() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::loadUrlAliasData
     */
    public function testLoadUrlaliasDataNonExistent()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_simple.php');
        $gateway = $this->getGateway();

        $rows = $gateway->loadUrlAliasData(array(md5('tri')));

        self::assertEmpty($rows);
    }

    /**
     * Test for the loadUrlAliasData() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::loadUrlAliasData
     */
    public function testLoadUrlaliasData()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_simple.php');
        $gateway = $this->getGateway();

        $row = $gateway->loadUrlAliasData(array(md5('jedan'), md5('dva')));

        self::assertEquals(
            array(
                'ezurlalias_ml0_id' => '2',
                'ezurlalias_ml0_link' => '2',
                'ezurlalias_ml0_is_alias' => '0',
                'ezurlalias_ml0_alias_redirects' => '1',
                'ezurlalias_ml0_is_original' => '1',
                'ezurlalias_ml0_action' => 'eznode:314',
                'ezurlalias_ml0_action_type' => 'eznode',
                'ezurlalias_ml0_lang_mask' => '2',
                'ezurlalias_ml0_text' => 'jedan',
                'ezurlalias_ml0_parent' => '0',
                'ezurlalias_ml0_text_md5' => '6896260129051a949051c3847c34466f',
                'id' => '3',
                'link' => '3',
                'is_alias' => '0',
                'alias_redirects' => '1',
                'is_original' => '1',
                'action' => 'eznode:315',
                'action_type' => 'eznode',
                'lang_mask' => '3',
                'text' => 'dva',
                'parent' => '2',
                'text_md5' => 'c67ed9a09ab136fae610b6a087d82e21',
            ),
            $row
        );
    }

    /**
     * Test for the loadUrlAliasData() method.
     *
     * Test with fixture containing language mask with multiple languages.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::loadUrlAliasData
     */
    public function testLoadUrlaliasDataMultipleLanguages()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_multilang.php');
        $gateway = $this->getGateway();

        $row = $gateway->loadUrlAliasData(array(md5('jedan'), md5('dva')));

        self::assertEquals(
            array(
                'ezurlalias_ml0_id' => '2',
                'ezurlalias_ml0_link' => '2',
                'ezurlalias_ml0_is_alias' => '0',
                'ezurlalias_ml0_alias_redirects' => '1',
                'ezurlalias_ml0_is_original' => '1',
                'ezurlalias_ml0_action' => 'eznode:314',
                'ezurlalias_ml0_action_type' => 'eznode',
                'ezurlalias_ml0_lang_mask' => '3',
                'ezurlalias_ml0_text' => 'jedan',
                'ezurlalias_ml0_parent' => '0',
                'ezurlalias_ml0_text_md5' => '6896260129051a949051c3847c34466f',
                'id' => '3',
                'link' => '3',
                'is_alias' => '0',
                'alias_redirects' => '1',
                'is_original' => '1',
                'action' => 'eznode:315',
                'action_type' => 'eznode',
                'lang_mask' => '6',
                'text' => 'dva',
                'parent' => '2',
                'text_md5' => 'c67ed9a09ab136fae610b6a087d82e21',
            ),
            $row
        );
    }

    /**
     * @return array
     */
    public function providerForTestLoadPathData()
    {
        return array(
            array(
                2,
                array(
                    array(
                        array('parent' => '0', 'lang_mask' => '3', 'text' => 'jedan'),
                    ),
                ),
            ),
            array(
                3,
                array(
                    array(
                        array('parent' => '0', 'lang_mask' => '3', 'text' => 'jedan'),
                    ),
                    array(
                        array('parent' => '2', 'lang_mask' => '5', 'text' => 'two'),
                        array('parent' => '2', 'lang_mask' => '3', 'text' => 'dva'),
                    ),
                ),
            ),
            array(
                4,
                array(
                    array(
                        array('parent' => '0', 'lang_mask' => '3', 'text' => 'jedan'),
                    ),
                    array(
                        array('parent' => '2', 'lang_mask' => '5', 'text' => 'two'),
                        array('parent' => '2', 'lang_mask' => '3', 'text' => 'dva'),
                    ),
                    array(
                        array('parent' => '3', 'lang_mask' => '9', 'text' => 'drei'),
                        array('parent' => '3', 'lang_mask' => '5', 'text' => 'three'),
                        array('parent' => '3', 'lang_mask' => '3', 'text' => 'tri'),
                    ),
                ),
            ),
        );
    }

    /**
     * Test for the loadPathData() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::loadPathData
     * @dataProvider providerForTestLoadPathData
     */
    public function testLoadPathData($id, $pathData)
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_fallback.php');
        $gateway = $this->getGateway();

        $loadedPathData = $gateway->loadPathData($id);

        self::assertEquals(
            $pathData,
            $loadedPathData
        );
    }

    /**
     * @return array
     */
    public function providerForTestLoadPathDataMultipleLanguages()
    {
        return array(
            array(
                2,
                array(
                    array(
                        array('parent' => '0', 'lang_mask' => '3', 'text' => 'jedan'),
                    ),
                ),
            ),
            array(
                3,
                array(
                    array(
                        array('parent' => '0', 'lang_mask' => '3', 'text' => 'jedan'),
                    ),
                    array(
                        array('parent' => '2', 'lang_mask' => '6', 'text' => 'dva'),
                    ),
                ),
            ),
            array(
                4,
                array(
                    array(
                        array('parent' => '0', 'lang_mask' => '3', 'text' => 'jedan'),
                    ),
                    array(
                        array('parent' => '2', 'lang_mask' => '6', 'text' => 'dva'),
                    ),
                    array(
                        array('parent' => '3', 'lang_mask' => '4', 'text' => 'three'),
                        array('parent' => '3', 'lang_mask' => '2', 'text' => 'tri'),
                    ),
                ),
            ),
        );
    }

    /**
     * Test for the loadPathData() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::loadPathData
     * @dataProvider providerForTestLoadPathDataMultipleLanguages
     */
    public function testLoadPathDataMultipleLanguages($id, $pathData)
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_multilang.php');
        $gateway = $this->getGateway();

        $loadedPathData = $gateway->loadPathData($id);

        self::assertEquals(
            $pathData,
            $loadedPathData
        );
    }

    /**
     * @return array
     */
    public function providerForTestCleanupAfterPublishHistorize()
    {
        return array(
            array(
                'action' => 'eznode:314',
                'languageId' => 2,
                'parentId' => 0,
                'textMD5' => '6896260129051a949051c3847c34466f',
            ),
            array(
                'action' => 'eznode:315',
                'languageId' => 2,
                'parentId' => 0,
                'textMD5' => 'c67ed9a09ab136fae610b6a087d82e21',
            ),
        );
    }

    /**
     * Test for the cleanupAfterPublish() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::cleanupAfterPublish
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::historize
     * @dataProvider providerForTestCleanupAfterPublishHistorize
     */
    public function testCleanupAfterPublishHistorize($action, $languageId, $parentId, $textMD5)
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_downgrade.php');
        $gateway = $this->getGateway();

        $loadedRow = $gateway->loadRow($parentId, $textMD5);

        $gateway->cleanupAfterPublish($action, $languageId, 42, $parentId, 'jabberwocky');

        $reloadedRow = $gateway->loadRow($parentId, $textMD5);
        $loadedRow['is_original'] = '0';
        $loadedRow['link'] = 42;
        $loadedRow['id'] = 6;

        self::assertEquals($reloadedRow, $loadedRow);
    }

    /**
     * @return array
     */
    public function providerForTestCleanupAfterPublishRemovesLanguage()
    {
        return array(
            array(
                'action' => 'eznode:316',
                'languageId' => 2,
                'parentId' => 0,
                'textMD5' => 'd2cfe69af2d64330670e08efb2c86df7',
            ),
            array(
                'action' => 'eznode:317',
                'languageId' => 2,
                'parentId' => 0,
                'textMD5' => '538dca05643d220317ad233cd7be7a0a',
            ),
        );
    }

    /**
     * Test for the cleanupAfterPublish() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::cleanupAfterPublish
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::removeTranslation
     * @dataProvider providerForTestCleanupAfterPublishRemovesLanguage
     */
    public function testCleanupAfterPublishRemovesLanguage($action, $languageId, $parentId, $textMD5)
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_downgrade.php');
        $gateway = $this->getGateway();

        $loadedRow = $gateway->loadRow($parentId, $textMD5);

        $gateway->cleanupAfterPublish($action, $languageId, 42, $parentId, 'jabberwocky');

        $reloadedRow = $gateway->loadRow($parentId, $textMD5);
        $loadedRow['lang_mask'] = $loadedRow['lang_mask'] & ~$languageId;

        self::assertEquals($reloadedRow, $loadedRow);
    }

    /**
     * Test for the reparent() method.
     *
     * @todo document
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::reparent
     */
    public function testReparent()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_simple.php');
        $gateway = $this->getGateway();

        $gateway->reparent(2, 42);

        self::assertEquals(
            array(
                'action' => 'eznode:315',
                'action_type' => 'eznode',
                'alias_redirects' => '1',
                'id' => '3',
                'is_alias' => '0',
                'is_original' => '1',
                'lang_mask' => '3',
                'link' => '3',
                'parent' => '42',
                'text' => 'dva',
                'text_md5' => 'c67ed9a09ab136fae610b6a087d82e21',
            ),
            $gateway->loadRow(42, 'c67ed9a09ab136fae610b6a087d82e21')
        );
    }

    /**
     * Test for the remove() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::remove
     */
    public function testRemove()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_remove.php');
        $gateway = $this->getGateway();

        $gateway->remove('eznode:314');

        self::assertEmpty($gateway->loadRow(0, 'd5189de027922f81005951e6efe0efd5'));
        self::assertEmpty($gateway->loadRow(0, 'a59d9f07e3d5fcf77911155650956a73'));
        self::assertEmpty($gateway->loadRow(0, '6449cba11bb134a57af94c8cb7f6c99c'));
        self::assertNotEmpty($gateway->loadRow(0, '0a06c09b6dd9a4606b4eb6d60ab188f0'));
        self::assertNotEmpty($gateway->loadRow(0, '82f2bce3283a0806a398fe78beda17d9'));
        self::assertNotEmpty($gateway->loadRow(0, '863d659d9fec68e5ab117b5f585a4ee7'));
    }

    /**
     * Test for the remove() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::remove
     */
    public function testRemoveWithId()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_remove.php');
        $gateway = $this->getGateway();

        $gateway->remove('eznode:315', 6);

        self::assertEmpty($gateway->loadRow(0, '0a06c09b6dd9a4606b4eb6d60ab188f0'));
        self::assertEmpty($gateway->loadRow(0, '82f2bce3283a0806a398fe78beda17d9'));
        self::assertNotEmpty($gateway->loadRow(0, '863d659d9fec68e5ab117b5f585a4ee7'));
        self::assertNotEmpty($gateway->loadRow(0, 'd5189de027922f81005951e6efe0efd5'));
        self::assertNotEmpty($gateway->loadRow(0, 'a59d9f07e3d5fcf77911155650956a73'));
        self::assertNotEmpty($gateway->loadRow(0, '6449cba11bb134a57af94c8cb7f6c99c'));
    }

    /**
     * Test for the removeCustomAlias() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::removeCustomAlias
     */
    public function testRemoveCustomAlias()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_remove.php');
        $gateway = $this->getGateway();

        $result = $gateway->removeCustomAlias(0, '6449cba11bb134a57af94c8cb7f6c99c');

        self::assertTrue($result);
        self::assertNotEmpty($gateway->loadRow(0, 'd5189de027922f81005951e6efe0efd5'));
        self::assertNotEmpty($gateway->loadRow(0, 'a59d9f07e3d5fcf77911155650956a73'));
        self::assertEmpty($gateway->loadRow(0, '6449cba11bb134a57af94c8cb7f6c99c'));
    }

    /**
     * Test for the removeByAction() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::removeCustomAlias
     */
    public function testRemoveCustomAliasFails()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_remove.php');
        $gateway = $this->getGateway();

        $result = $gateway->removeCustomAlias(0, 'd5189de027922f81005951e6efe0efd5');

        self::assertFalse($result);
        self::assertNotEmpty($gateway->loadRow(0, 'd5189de027922f81005951e6efe0efd5'));
    }

    /**
     * Test for the getNextId() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase::getNextId
     */
    public function testGetNextId()
    {
        $gateway = $this->getGateway();

        self::assertEquals(1, $gateway->getNextId());
        self::assertEquals(2, $gateway->getNextId());
    }

    /**
     * Returns the DoctrineDatabase gateway to test.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase
     */
    protected function getGateway()
    {
        if (!isset($this->gateway)) {
            $languageHandler = new LanguageHandler(
                new LanguageGateway(
                    $this->getDatabaseHandler()
                ),
                new LanguageMapper()
            );
            $this->gateway = new DoctrineDatabase(
                $this->getDatabaseHandler(),
                new LanguageMaskGenerator($languageHandler)
            );
        }

        return $this->gateway;
    }
}
