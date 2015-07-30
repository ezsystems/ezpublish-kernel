<?php

/**
 * File containing the LanguagesTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Languages;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Symfony\Component\Yaml\Yaml;

class LanguagesTest extends AbstractParserTestCase
{
    protected function getContainerExtensions()
    {
        return array(new EzPublishCoreExtension(array(new Languages())));
    }

    protected function getMinimalConfiguration()
    {
        return $this->minimalConfig = Yaml::parse(file_get_contents(__DIR__ . '/../../Fixtures/ezpublish_minimal.yml'));
    }

    public function testLanguagesSingleSiteaccess()
    {
        $langDemoSite = array('eng-GB');
        $langFre = array('fre-FR', 'eng-GB');
        $config = array(
            'siteaccess' => array(
                'list' => array('fre2'),
            ),
            'system' => array(
                'ezdemo_site' => array('languages' => $langDemoSite),
                'fre' => array('languages' => $langFre),
                'fre2' => array('languages' => $langFre),
            ),
        );
        $this->load($config);

        $this->assertConfigResolverParameterValue('languages', $langDemoSite, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('languages', $langFre, 'fre');
        $this->assertConfigResolverParameterValue('languages', $langFre, 'fre2');
        $this->assertSame(
            array(
                'eng-GB' => array('ezdemo_site'),
                'fre-FR' => array('fre', 'fre2'),
            ),
            $this->container->getParameter('ezpublish.siteaccesses_by_language')
        );
        // languages for ezdemo_site_admin will take default value (empty array)
        $this->assertConfigResolverParameterValue('languages', array(), 'ezdemo_site_admin');
    }

    public function testLanguagesSiteaccessGroup()
    {
        $langDemoSite = array('eng-US', 'eng-GB');
        $config = array(
            'system' => array(
                'ezdemo_frontend_group' => array('languages' => $langDemoSite),
                'ezdemo_site' => array(),
                'fre' => array(),
            ),
        );
        $this->load($config);

        $this->assertConfigResolverParameterValue('languages', $langDemoSite, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('languages', $langDemoSite, 'fre');
        $this->assertSame(
            array(
                'eng-US' => array('ezdemo_site', 'fre'),
            ),
            $this->container->getParameter('ezpublish.siteaccesses_by_language')
        );
        // languages for ezdemo_site_admin will take default value (empty array)
        $this->assertConfigResolverParameterValue('languages', array(), 'ezdemo_site_admin');
    }

    public function testTranslationSiteAccesses()
    {
        $translationSAsDemoSite = array('foo', 'bar');
        $translationSAsFre = array('foo2', 'bar2');
        $config = array(
            'system' => array(
                'ezdemo_site' => array('translation_siteaccesses' => $translationSAsDemoSite),
                'fre' => array('translation_siteaccesses' => $translationSAsFre),
            ),
        );
        $this->load($config);

        $this->assertConfigResolverParameterValue('translation_siteaccesses', $translationSAsDemoSite, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('translation_siteaccesses', $translationSAsFre, 'fre');
        $this->assertConfigResolverParameterValue('translation_siteaccesses', array(), 'ezdemo_site_admin');
    }

    public function testTranslationSiteAccessesWithGroup()
    {
        $translationSAsDemoSite = array('ezdemo_site', 'fre');
        $config = array(
            'system' => array(
                'ezdemo_frontend_group' => array('translation_siteaccesses' => $translationSAsDemoSite),
                'ezdemo_site' => array(),
                'fre' => array(),
            ),
        );
        $this->load($config);

        $this->assertConfigResolverParameterValue('translation_siteaccesses', $translationSAsDemoSite, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('translation_siteaccesses', $translationSAsDemoSite, 'fre');
        $this->assertConfigResolverParameterValue('translation_siteaccesses', array(), 'ezdemo_site_admin');
    }
}
