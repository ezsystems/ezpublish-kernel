<?php

/**
 * File containing the LanguagesTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Languages;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Symfony\Component\Yaml\Yaml;

class LanguagesTest extends AbstractParserTestCase
{
    protected function getContainerExtensions()
    {
        return [new EzPublishCoreExtension([new Languages()])];
    }

    protected function getMinimalConfiguration()
    {
        return $this->minimalConfig = Yaml::parse(file_get_contents(__DIR__ . '/../../Fixtures/ezpublish_minimal.yml'));
    }

    public function testLanguagesSingleSiteaccess()
    {
        $langDemoSite = ['eng-GB'];
        $langFre = ['fre-FR', 'eng-GB'];
        $config = [
            'siteaccess' => [
                'list' => ['fre2'],
            ],
            'system' => [
                'ezdemo_site' => ['languages' => $langDemoSite],
                'fre' => ['languages' => $langFre],
                'fre2' => ['languages' => $langFre],
            ],
        ];
        $this->load($config);

        $this->assertConfigResolverParameterValue('languages', $langDemoSite, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('languages', $langFre, 'fre');
        $this->assertConfigResolverParameterValue('languages', $langFre, 'fre2');
        $this->assertSame(
            [
                'eng-GB' => ['ezdemo_site'],
                'fre-FR' => ['fre', 'fre2'],
            ],
            $this->container->getParameter('ezpublish.siteaccesses_by_language')
        );
        // languages for ezdemo_site_admin will take default value (empty array)
        $this->assertConfigResolverParameterValue('languages', [], 'ezdemo_site_admin');
    }

    public function testLanguagesSiteaccessGroup()
    {
        $langDemoSite = ['eng-US', 'eng-GB'];
        $config = [
            'system' => [
                'ezdemo_frontend_group' => ['languages' => $langDemoSite],
                'ezdemo_site' => [],
                'fre' => [],
            ],
        ];
        $this->load($config);

        $this->assertConfigResolverParameterValue('languages', $langDemoSite, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('languages', $langDemoSite, 'fre');
        $this->assertSame(
            [
                'eng-US' => ['ezdemo_site', 'fre'],
            ],
            $this->container->getParameter('ezpublish.siteaccesses_by_language')
        );
        // languages for ezdemo_site_admin will take default value (empty array)
        $this->assertConfigResolverParameterValue('languages', [], 'ezdemo_site_admin');
    }

    public function testTranslationSiteAccesses()
    {
        $translationSAsDemoSite = ['foo', 'bar'];
        $translationSAsFre = ['foo2', 'bar2'];
        $config = [
            'system' => [
                'ezdemo_site' => ['translation_siteaccesses' => $translationSAsDemoSite],
                'fre' => ['translation_siteaccesses' => $translationSAsFre],
            ],
        ];
        $this->load($config);

        $this->assertConfigResolverParameterValue('translation_siteaccesses', $translationSAsDemoSite, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('translation_siteaccesses', $translationSAsFre, 'fre');
        $this->assertConfigResolverParameterValue('translation_siteaccesses', [], 'ezdemo_site_admin');
    }

    public function testTranslationSiteAccessesWithGroup()
    {
        $translationSAsDemoSite = ['ezdemo_site', 'fre'];
        $config = [
            'system' => [
                'ezdemo_frontend_group' => ['translation_siteaccesses' => $translationSAsDemoSite],
                'ezdemo_site' => [],
                'fre' => [],
            ],
        ];
        $this->load($config);

        $this->assertConfigResolverParameterValue('translation_siteaccesses', $translationSAsDemoSite, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('translation_siteaccesses', $translationSAsDemoSite, 'fre');
        $this->assertConfigResolverParameterValue('translation_siteaccesses', [], 'ezdemo_site_admin');
    }
}
