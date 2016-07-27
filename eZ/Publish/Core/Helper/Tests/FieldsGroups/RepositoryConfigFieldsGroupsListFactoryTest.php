<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Helper\Tests\FieldsGroups;

use eZ\Publish\Core\Helper\FieldsGroups\RepositoryConfigFieldsGroupsListFactory;
use PHPUnit_Framework_TestCase;

class RepositoryConfigFieldsGroupsListFactoryTest extends PHPUnit_Framework_TestCase
{
    private $repositoryConfigMock;

    private $translatorMock;

    public function testBuild()
    {
        $this->getRepositoryConfigMock()
            ->expects($this->once())
            ->method('getRepositoryConfig')
            ->willReturn(['fields_groups' => ['list' => ['group_a', 'group_b'], 'default' => 'group_a']]);

        $this->getTranslatorMock()
            ->expects($this->any())
            ->method('trans')
            ->will($this->returnArgument(0));

        $factory = new RepositoryConfigFieldsGroupsListFactory($this->getRepositoryConfigMock());
        $list = $factory->build($this->getTranslatorMock());

        self::assertEquals(['group_a' => 'group_a', 'group_b' => 'group_b'], $list->getGroups());
        self::assertEquals('group_a', $list->getDefaultGroup());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider
     */
    protected function getRepositoryConfigMock()
    {
        if (!isset($this->repositoryConfigMock)) {
            $this->repositoryConfigMock = $this
                ->getMockBuilder('eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider')
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->repositoryConfigMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getTranslatorMock()
    {
        if (!isset($this->translatorMock)) {
            $this->translatorMock = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        }

        return $this->translatorMock;
    }
}
