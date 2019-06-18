<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\UrlWildcardTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard as SPIURLWildcard;
use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;

/**
 * Mock Test case for UrlWildcard Service.
 */
class UrlWildcardTest extends BaseServiceMockTest
{
    /**
     * Test for the __construct() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::__construct
     */
    public function testConstructor()
    {
        $service = $this->getPartlyMockedURLWildcardService();

        self::assertAttributeSame($this->getRepositoryMock(), 'repository', $service);
        self::assertAttributeSame($this->getPersistenceMockHandler('Content\\UrlWildcard\\Handler'), 'urlWildcardHandler', $service);
        self::assertAttributeSame([], 'settings', $service);
    }

    /**
     * Test for the create() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::create
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateThrowsUnauthorizedException()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        $repositoryMock = $this->getRepositoryMock();
        $repositoryMock->expects(
            $this->once()
        )->method(
            'hasAccess'
        )->with(
            $this->equalTo('content'),
            $this->equalTo('urltranslator')
        )->will(
            $this->returnValue(false)
        );

        $mockedService->create('lorem/ipsum', 'opossum', true);
    }

    /**
     * Test for the create() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::create
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateThrowsInvalidArgumentException()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();
        $repositoryMock = $this->getRepositoryMock();

        $repositoryMock->expects(
            $this->once()
        )->method(
            'hasAccess'
        )->with(
            $this->equalTo('content'),
            $this->equalTo('urltranslator')
        )->will(
            $this->returnValue(true)
        );

        $handlerMock->expects(
            $this->once()
        )->method(
            'loadAll'
        )->will(
            $this->returnValue(
                [
                    new SPIURLWildcard(['sourceUrl' => '/lorem/ipsum']),
                ]
            )
        );

        $mockedService->create('/lorem/ipsum', 'opossum', true);
    }

    public function providerForTestCreateThrowsContentValidationException()
    {
        return [
            ['fruit', 'food/{1}', true],
            ['fruit/*', 'food/{2}', false],
            ['fruit/*/*', 'food/{3}', true],
        ];
    }

    /**
     * Test for the create() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::create
     * @dataProvider providerForTestCreateThrowsContentValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testCreateThrowsContentValidationException($sourceUrl, $destinationUrl, $forward)
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();
        $repositoryMock = $this->getRepositoryMock();

        $repositoryMock->expects(
            $this->once()
        )->method(
            'hasAccess'
        )->with(
            $this->equalTo('content'),
            $this->equalTo('urltranslator')
        )->will(
            $this->returnValue(true)
        );

        $handlerMock->expects(
            $this->once()
        )->method(
            'loadAll'
        )->will(
            $this->returnValue([])
        );

        $mockedService->create($sourceUrl, $destinationUrl, $forward);
    }

    public function providerForTestCreate()
    {
        return [
            ['fruit', 'food', true],
            [' /fruit/ ', ' /food/ ', true],
            ['/fruit/*', '/food', false],
            ['/fruit/*', '/food/{1}', true],
            ['/fruit/*/*', '/food/{1}', true],
            ['/fruit/*/*', '/food/{2}', true],
            ['/fruit/*/*', '/food/{1}/{2}', true],
        ];
    }

    /**
     * Test for the create() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::create
     * @dataProvider providerForTestCreate
     */
    public function testCreate($sourceUrl, $destinationUrl, $forward)
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();
        $repositoryMock = $this->getRepositoryMock();

        $sourceUrl = '/' . trim($sourceUrl, '/ ');
        $destinationUrl = '/' . trim($destinationUrl, '/ ');

        $repositoryMock->expects(
            $this->once()
        )->method(
            'hasAccess'
        )->with(
            $this->equalTo('content'),
            $this->equalTo('urltranslator')
        )->will(
            $this->returnValue(true)
        );

        $repositoryMock->expects($this->once())->method('beginTransaction');
        $repositoryMock->expects($this->once())->method('commit');

        $handlerMock->expects(
            $this->once()
        )->method(
            'loadAll'
        )->will(
            $this->returnValue([])
        );

        $handlerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($sourceUrl),
            $this->equalTo($destinationUrl),
            $this->equalTo($forward)
        )->will(
            $this->returnValue(
                new SPIURLWildcard(
                    [
                        'id' => 123456,
                        'sourceUrl' => $sourceUrl,
                        'destinationUrl' => $destinationUrl,
                        'forward' => $forward,
                    ]
                )
            )
        );

        $urlWildCard = $mockedService->create($sourceUrl, $destinationUrl, $forward);

        $this->assertEquals(
            new URLWildcard(
                [
                    'id' => 123456,
                    'sourceUrl' => $sourceUrl,
                    'destinationUrl' => $destinationUrl,
                    'forward' => $forward,
                ]
            ),
            $urlWildCard
        );
    }

    /**
     * Test for the create() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::create
     * @expectedException \Exception
     */
    public function testCreateWithRollback()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();
        $repositoryMock = $this->getRepositoryMock();

        $repositoryMock->expects(
            $this->once()
        )->method(
            'hasAccess'
        )->with(
            $this->equalTo('content'),
            $this->equalTo('urltranslator')
        )->will(
            $this->returnValue(true)
        );

        $repositoryMock->expects($this->once())->method('beginTransaction');
        $repositoryMock->expects($this->once())->method('rollback');

        $handlerMock->expects(
            $this->once()
        )->method(
            'loadAll'
        )->will(
            $this->returnValue([])
        );

        $sourceUrl = '/lorem';
        $destinationUrl = '/ipsum';
        $forward = true;

        $handlerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($sourceUrl),
            $this->equalTo($destinationUrl),
            $this->equalTo($forward)
        )->will(
            $this->throwException(new \Exception())
        );

        $mockedService->create($sourceUrl, $destinationUrl, $forward);
    }

    /**
     * Test for the remove() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::remove
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testRemoveThrowsUnauthorizedException()
    {
        $wildcard = new URLWildcard(['id' => 'McBoom']);

        $mockedService = $this->getPartlyMockedURLWildcardService();
        $repositoryMock = $this->getRepositoryMock();
        $repositoryMock->expects(
            $this->once()
        )->method(
            'canUser'
        )->with(
            $this->equalTo('content'),
            $this->equalTo('urltranslator'),
            $this->equalTo($wildcard)
        )->will(
            $this->returnValue(false)
        );

        $repositoryMock->expects($this->never())->method('beginTransaction');

        $mockedService->remove($wildcard);
    }

    /**
     * Test for the remove() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::remove
     */
    public function testRemove()
    {
        $wildcard = new URLWildcard(['id' => 'McBomb']);

        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();
        $repositoryMock = $this->getRepositoryMock();

        $repositoryMock->expects(
            $this->once()
        )->method(
            'canUser'
        )->with(
            $this->equalTo('content'),
            $this->equalTo('urltranslator'),
            $this->equalTo($wildcard)
        )->will(
            $this->returnValue(true)
        );

        $repositoryMock->expects($this->once())->method('beginTransaction');
        $repositoryMock->expects($this->once())->method('commit');

        $handlerMock->expects(
            $this->once()
        )->method(
            'remove'
        )->with(
            $this->equalTo('McBomb')
        );

        $mockedService->remove($wildcard);
    }

    /**
     * Test for the remove() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::remove
     * @expectedException \Exception
     */
    public function testRemoveWithRollback()
    {
        $wildcard = new URLWildcard(['id' => 'McBoo']);

        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();
        $repositoryMock = $this->getRepositoryMock();

        $repositoryMock->expects(
            $this->once()
        )->method(
            'canUser'
        )->with(
            $this->equalTo('content'),
            $this->equalTo('urltranslator'),
            $this->equalTo($wildcard)
        )->will(
            $this->returnValue(true)
        );

        $repositoryMock->expects($this->once())->method('beginTransaction');
        $repositoryMock->expects($this->once())->method('rollback');

        $handlerMock->expects(
            $this->once()
        )->method(
            'remove'
        )->with(
            $this->equalTo('McBoo')
        )->will(
            $this->throwException(new \Exception())
        );

        $mockedService->remove($wildcard);
    }

    /**
     * Test for the load() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::remove
     * @expectedException \Exception
     */
    public function testLoadThrowsException()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();

        $handlerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $this->equalTo('Luigi')
        )->will(
            $this->throwException(new \Exception())
        );

        $mockedService->load('Luigi');
    }

    /**
     * Test for the load() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::remove
     */
    public function testLoad()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();

        $handlerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $this->equalTo('Luigi')
        )->will(
            $this->returnValue(
                new SPIURLWildcard(
                    [
                        'id' => 'Luigi',
                        'sourceUrl' => 'this',
                        'destinationUrl' => 'that',
                        'forward' => true,
                    ]
                )
            )
        );

        $urlWildcard = $mockedService->load('Luigi');

        $this->assertEquals(
            new URLWildcard(
                [
                    'id' => 'Luigi',
                    'sourceUrl' => 'this',
                    'destinationUrl' => 'that',
                    'forward' => true,
                ]
            ),
            $urlWildcard
        );
    }

    /**
     * Test for the loadAll() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::loadAll
     */
    public function testLoadAll()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();

        $handlerMock->expects(
            $this->once()
        )->method(
            'loadAll'
        )->with(
            $this->equalTo(0),
            $this->equalTo(-1)
        )->will(
            $this->returnValue([])
        );

        $mockedService->loadAll();
    }

    /**
     * Test for the loadAll() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::loadAll
     */
    public function testLoadAllWithLimitAndOffset()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();

        $handlerMock->expects(
            $this->once()
        )->method(
            'loadAll'
        )->with(
            $this->equalTo(12),
            $this->equalTo(34)
        )->will(
            $this->returnValue(
                [
                    new SPIURLWildcard(
                        [
                            'id' => 'Luigi',
                            'sourceUrl' => 'this',
                            'destinationUrl' => 'that',
                            'forward' => true,
                        ]
                    ),
                ]
            )
        );

        $urlWildcards = $mockedService->loadAll(12, 34);

        $this->assertEquals(
            [
                new URLWildcard(
                    [
                        'id' => 'Luigi',
                        'sourceUrl' => 'this',
                        'destinationUrl' => 'that',
                        'forward' => true,
                    ]
                ),
            ],
            $urlWildcards
        );
    }

    /**
     * @return array
     */
    public function providerForTestTranslateThrowsNotFoundException()
    {
        return [
            [
                [
                    'sourceUrl' => '/fruit',
                    'destinationUrl' => '/food',
                    'forward' => true,
                ],
                '/vegetable',
            ],
            [
                [
                    'sourceUrl' => '/fruit/apricot',
                    'destinationUrl' => '/food/apricot',
                    'forward' => true,
                ],
                '/fruit/lemon',
            ],
            [
                [
                    'sourceUrl' => '/fruit/*',
                    'destinationUrl' => '/food/{1}',
                    'forward' => true,
                ],
                '/fruit',
            ],
            [
                [
                    'sourceUrl' => '/fruit/*/*',
                    'destinationUrl' => '/food/{1}/{2}',
                    'forward' => true,
                ],
                '/fruit/citrus',
            ],
        ];
    }

    /**
     * Test for the translate() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::translate
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @dataProvider providerForTestTranslateThrowsNotFoundException
     */
    public function testTranslateThrowsNotFoundException($createArray, $url)
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();

        $handlerMock->expects(
            $this->once()
        )->method(
            'loadAll'
        )->with(
            $this->equalTo(0),
            $this->equalTo(-1)
        )->will(
            $this->returnValue([new SPIURLWildcard($createArray)])
        );

        $mockedService->translate($url);
    }

    /**
     * @return array
     */
    public function providerForTestTranslate()
    {
        return [
            [
                [
                    'sourceUrl' => '/fruit/apricot',
                    'destinationUrl' => '/food/apricot',
                    'forward' => true,
                ],
                '/fruit/apricot',
                '/food/apricot',
            ],
            [
                [
                    'sourceUrl' => '/fruit/*',
                    'destinationUrl' => '/food/{1}',
                    'forward' => true,
                ],
                '/fruit/citrus',
                '/food/citrus',
            ],
            [
                [
                    'sourceUrl' => '/fruit/*',
                    'destinationUrl' => '/food/{1}',
                    'forward' => true,
                ],
                '/fruit/citrus/orange',
                '/food/citrus/orange',
            ],
            [
                [
                    'sourceUrl' => '/fruit/*/*',
                    'destinationUrl' => '/food/{2}',
                    'forward' => true,
                ],
                '/fruit/citrus/orange',
                '/food/orange',
            ],
            [
                [
                    'sourceUrl' => '/fruit/*/*',
                    'destinationUrl' => '/food/{1}/{2}',
                    'forward' => true,
                ],
                '/fruit/citrus/orange',
                '/food/citrus/orange',
            ],
            [
                [
                    'sourceUrl' => '/fruit/*/pamplemousse',
                    'destinationUrl' => '/food/weird',
                    'forward' => true,
                ],
                '/fruit/citrus/pamplemousse',
                '/food/weird',
            ],
            [
                [
                    'sourceUrl' => '/fruit/*/pamplemousse',
                    'destinationUrl' => '/food/weird/{1}',
                    'forward' => true,
                ],
                '/fruit/citrus/pamplemousse',
                '/food/weird/citrus',
            ],
            [
                [
                    'sourceUrl' => '/fruit/*/pamplemousse',
                    'destinationUrl' => '/food/weird/{1}',
                    'forward' => true,
                ],
                '/fruit/citrus/yellow/pamplemousse',
                '/food/weird/citrus/yellow',
            ],
        ];
    }

    /**
     * Test for the translate() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::translate
     * @dataProvider providerForTestTranslate
     */
    public function testTranslate($createArray, $url, $uri)
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();

        $handlerMock->expects(
            $this->once()
        )->method(
            'loadAll'
        )->with(
            $this->equalTo(0),
            $this->equalTo(-1)
        )->will(
            $this->returnValue([new SPIURLWildcard($createArray)])
        );

        $translationResult = $mockedService->translate($url);

        $this->assertEquals(
            new URLWildcardTranslationResult(
                [
                    'uri' => $uri,
                    'forward' => $createArray['forward'],
                ]
            ),
            $translationResult
        );
    }

    /**
     * Test for the translate() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::translate
     */
    public function testTranslateUsesLongestMatchingWildcard()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();

        $handlerMock->expects(
            $this->once()
        )->method(
            'loadAll'
        )->with(
            $this->equalTo(0),
            $this->equalTo(-1)
        )->will(
            $this->returnValue(
                [
                    new SPIURLWildcard(
                        [
                            'sourceUrl' => '/something/*',
                            'destinationUrl' => '/short',
                            'forward' => true,
                        ]
                    ),
                    new SPIURLWildcard(
                        [
                            'sourceUrl' => '/something/something/*',
                            'destinationUrl' => '/long',
                            'forward' => false,
                        ]
                    ),
                ]
            )
        );

        $translationResult = $mockedService->translate('/something/something/thing');

        $this->assertEquals(
            new URLWildcardTranslationResult(
                [
                    'uri' => '/long',
                    'forward' => false,
                ]
            ),
            $translationResult
        );
    }

    /**
     * Returns the content service to test with $methods mocked.
     *
     * Injected Repository comes from {@see getRepositoryMock()} and persistence handler from {@see getPersistenceMock()}
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Repository\URLWildcardService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedURLWildcardService(array $methods = null)
    {
        return $this->getMock(
            'eZ\\Publish\\Core\\Repository\\URLWildcardService',
            $methods,
            [
                $this->getRepositoryMock(),
                $this->getPersistenceMock()->urlWildcardHandler(),
            ]
        );
    }
}
