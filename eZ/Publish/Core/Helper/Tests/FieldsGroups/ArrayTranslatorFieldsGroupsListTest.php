<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Helper\Tests\FieldsGroups;

use eZ\Publish\Core\Helper\FieldsGroups\ArrayTranslatorFieldsGroupsList;
use Symfony\Component\Translation\TranslatorInterface;
use PHPUnit\Framework\TestCase;

class ArrayTranslatorFieldsGroupsListTest extends TestCase
{
    private $translatorMock;

    public function testTranslatesGroups()
    {
        $groups = ['slayer', 'system_of_a_down'];
        $default = 'system_of_a_down';

        $this->getTranslatorMock()
            ->expects($this->any())
            ->method('trans')
            ->will(
                $this->returnValueMap([
                    ['slayer', [], 'ezplatform_fields_groups', null, 'Slayer'],
                    ['system_of_a_down', [], 'ezplatform_fields_groups', null, 'System of a down'],
                ])
            );

        $list = $this->buildList($groups, $default);

        self::assertEquals(
            [
                'slayer' => 'Slayer',
                'system_of_a_down' => 'System of a down',
            ],
            $list->getGroups()
        );
    }

    public function testReturnsDefault()
    {
        $list = $this->buildList([], 'A');

        self::assertEquals('A', $list->getDefaultGroup());
    }

    public function testUsesIdentifierIfNoTranslation()
    {
        $groups = ['slayer', 'system_of_a_down'];
        $default = 'system_of_a_down';

        $this->getTranslatorMock()
            ->expects($this->any())
            ->method('trans')
            ->will($this->returnArgument(0));

        $list = $this->buildList($groups, $default);

        self::assertEquals(
            [
                'slayer' => 'slayer',
                'system_of_a_down' => 'system_of_a_down',
            ],
            $list->getGroups()
        );
    }

    private function buildList($groups, $default)
    {
        return new ArrayTranslatorFieldsGroupsList(
            $this->getTranslatorMock(),
            $default,
            $groups
        );
    }

    /**
     * @return \Symfony\Component\Translation\TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getTranslatorMock()
    {
        if ($this->translatorMock === null) {
            $this->translatorMock = $this->createMock(TranslatorInterface::class);
        }

        return $this->translatorMock;
    }
}
