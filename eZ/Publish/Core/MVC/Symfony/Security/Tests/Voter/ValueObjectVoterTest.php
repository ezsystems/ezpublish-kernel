<?php

/**
 * File containing the ValueObjectVoterTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\Voter;

use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Voter\ValueObjectVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use PHPUnit\Framework\TestCase;

class ValueObjectVoterTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\Repository
     */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this->getMock('eZ\Publish\API\Repository\Repository');
    }

    /**
     * @dataProvider supportsAttributeProvider
     */
    public function testSupportsAttribute($attribute, $expectedResult)
    {
        $voter = new ValueObjectVoter($this->repository);
        $this->assertSame($expectedResult, $voter->supportsAttribute($attribute));
    }

    public function supportsAttributeProvider()
    {
        return [
            ['foo', false],
            [new Attribute('foo', 'bar'), false],
            [new Attribute('foo', 'bar', ['some' => 'thing']), false],
            [new \stdClass(), false],
            [['foo'], false],
            [
                new Attribute(
                    'foo',
                    'bar',
                    ['valueObject' => $this->getMockForAbstractClass('eZ\Publish\API\Repository\Values\ValueObject')]
                ),
                true,
            ],
        ];
    }

    /**
     * @dataProvider supportsClassProvider
     */
    public function testSupportsClass($class)
    {
        $voter = new ValueObjectVoter($this->repository);
        $this->assertTrue($voter->supportsClass($class));
    }

    public function supportsClassProvider()
    {
        return [
            ['foo'],
            ['bar'],
            ['eZ\Publish\API\Repository\Values\ValueObject'],
            ['eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController'],
        ];
    }

    /**
     * @dataProvider voteInvalidAttributeProvider
     */
    public function testVoteInvalidAttribute(array $attributes)
    {
        $voter = new ValueObjectVoter($this->repository);
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote(
                $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface'),
                new \stdClass(),
                $attributes
            )
        );
    }

    public function voteInvalidAttributeProvider()
    {
        return [
            [[]],
            [['foo']],
            [['foo', 'bar', ['some' => 'thing']]],
            [[new \stdClass()]],
            [[new Attribute('content', 'read')]],
        ];
    }

    /**
     * @dataProvider voteProvider
     */
    public function testVote(Attribute $attribute, $repositoryCanUser, $expectedResult)
    {
        $voter = new ValueObjectVoter($this->repository);
        $targets = isset($attribute->limitations['targets']) ? $attribute->limitations['targets'] : null;
        $this->repository
            ->expects($this->once())
            ->method('canUser')
            ->with($attribute->module, $attribute->function, $attribute->limitations['valueObject'], $targets)
            ->will($this->returnValue($repositoryCanUser));

        $this->assertSame(
            $expectedResult,
            $voter->vote(
                $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface'),
                new \stdClass(),
                [$attribute]
            )
        );
    }

    public function voteProvider()
    {
        return [
            [
                new Attribute('content', 'read', ['valueObject' => $this->getMockForAbstractClass('eZ\Publish\API\Repository\Values\ValueObject')]),
                true,
                VoterInterface::ACCESS_GRANTED,
            ],
            [
                new Attribute('content', 'read', ['valueObject' => $this->getMockForAbstractClass('eZ\Publish\API\Repository\Values\ValueObject')]),
                false,
                VoterInterface::ACCESS_DENIED,
            ],
            [
                new Attribute(
                    'content',
                    'read',
                    [
                        'valueObject' => $this->getMockForAbstractClass('eZ\Publish\API\Repository\Values\ValueObject'),
                        'targets' => $this->getMockForAbstractClass('eZ\Publish\API\Repository\Values\ValueObject'),
                    ]
                ),
                true,
                VoterInterface::ACCESS_GRANTED,
            ],
            [
                new Attribute(
                    'content',
                    'read',
                    [
                        'valueObject' => $this->getMockForAbstractClass('eZ\Publish\API\Repository\Values\ValueObject'),
                        'targets' => [$this->getMockForAbstractClass('eZ\Publish\API\Repository\Values\ValueObject')],
                    ]
                ),
                true,
                VoterInterface::ACCESS_GRANTED,
            ],
            [
                new Attribute(
                    'content',
                    'read',
                    [
                        'valueObject' => $this->getMockForAbstractClass('eZ\Publish\API\Repository\Values\ValueObject'),
                        'targets' => $this->getMockForAbstractClass('eZ\Publish\API\Repository\Values\ValueObject'),
                    ]
                ),
                false,
                VoterInterface::ACCESS_DENIED,
            ],
            [
                new Attribute(
                    'content',
                    'read',
                    [
                        'valueObject' => $this->getMockForAbstractClass('eZ\Publish\API\Repository\Values\ValueObject'),
                        'targets' => [$this->getMockForAbstractClass('eZ\Publish\API\Repository\Values\ValueObject')],
                    ]
                ),
                false,
                VoterInterface::ACCESS_DENIED,
            ],
        ];
    }
}
