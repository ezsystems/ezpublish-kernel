<?php
/**
 * File containing the LanguagesTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Languages;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Yaml\Yaml;

class LanguagesTest extends AbstractExtensionTestCase
{
    /**
     * Return an array of container extensions you need to be registered for each test (usually just the container
     * extension you are testing.
     *
     * @return ExtensionInterface[]
     */
    protected function getContainerExtensions()
    {
        return array( new EzPublishCoreExtension( array( new Languages() ) ) );
    }

    protected function getMinimalConfiguration()
    {
        return $this->minimalConfig = Yaml::parse( file_get_contents( __DIR__ . '/../../Fixtures/ezpublish_minimal.yml' ) );
    }

    public function testLanguagesSingleSiteaccess()
    {
        $langDemoSite = array( 'eng-GB' );
        $langFre = array( 'fre-FR', 'eng-GB' );
        $config = array(
            'siteaccess' => array(
                'list' => array( 'fre2' )
            ),
            'system' => array(
                'ezdemo_site' => array( 'languages' => $langDemoSite ),
                'fre' => array( 'languages' => $langFre ),
                'fre2' => array( 'languages' => $langFre ),
            )
        );
        $this->load( $config );

        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.languages' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.fre.languages' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.fre2.languages' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.global.languages' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site_admin.languages' ) );
        $this->assertSame( $langDemoSite, $this->container->getParameter( 'ezsettings.ezdemo_site.languages' ) );
        $this->assertSame( $langFre, $this->container->getParameter( 'ezsettings.fre.languages' ) );
        $this->assertSame( $langFre, $this->container->getParameter( 'ezsettings.fre2.languages' ) );
        $this->assertSame(
            array(
                'eng-GB' => array( 'ezdemo_site' ),
                'fre-FR' => array( 'fre', 'fre2' ),
            ),
            $this->container->getParameter( 'ezpublish.siteaccesses_by_language' )
        );
        // languages for ezdemo_site_admin will take default value (empty array)
        $this->assertEmpty( $this->container->getParameter( 'ezsettings.ezdemo_site_admin.languages' ) );
    }

    public function testLanguagesSiteaccessGroup()
    {
        $langDemoSite = array( 'eng-US', 'eng-GB' );
        $config = array(
            'system' => array(
                'ezdemo_frontend_group' => array( 'languages' => $langDemoSite ),
                'ezdemo_site' => array(),
                'fre' => array(),
            )
        );
        $this->load( $config );

        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.languages' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.fre.languages' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.global.languages' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site_admin.languages' ) );
        $this->assertSame( $langDemoSite, $this->container->getParameter( 'ezsettings.ezdemo_site.languages' ) );
        $this->assertSame( $langDemoSite, $this->container->getParameter( 'ezsettings.fre.languages' ) );
        $this->assertSame(
            array(
                'eng-US' => array( 'ezdemo_site', 'fre' ),
            ),
            $this->container->getParameter( 'ezpublish.siteaccesses_by_language' )
        );
        // languages for ezdemo_site_admin will take default value (empty array)
        $this->assertEmpty( $this->container->getParameter( 'ezsettings.ezdemo_site_admin.languages' ) );
    }

    public function testTranslationSiteAccesses()
    {
        $translationSAsDemoSite = array( 'foo', 'bar' );
        $translationSAsFre = array( 'foo2', 'bar2' );
        $config = array(
            'system' => array(
                'ezdemo_site' => array( 'translation_siteaccesses' => $translationSAsDemoSite ),
                'fre' => array( 'translation_siteaccesses' => $translationSAsFre ),
            )
        );
        $this->load( $config );

        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.translation_siteaccesses' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.fre.translation_siteaccesses' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.global.translation_siteaccesses' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site_admin.translation_siteaccesses' ) );
        $this->assertSame( $translationSAsDemoSite, $this->container->getParameter( 'ezsettings.ezdemo_site.translation_siteaccesses' ) );
        $this->assertSame( $translationSAsFre, $this->container->getParameter( 'ezsettings.fre.translation_siteaccesses' ) );
        $this->assertEmpty( $this->container->getParameter( 'ezsettings.ezdemo_site_admin.translation_siteaccesses' ) );
    }

    public function testTranslationSiteAccessesWithGroup()
    {
        $translationSAsDemoSite = array( 'ezdemo_site', 'fre' );
        $config = array(
            'system' => array(
                'ezdemo_frontend_group' => array( 'translation_siteaccesses' => $translationSAsDemoSite ),
                'ezdemo_site' => array(),
                'fre' => array(),
            )
        );
        $this->load( $config );

        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site.translation_siteaccesses' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.fre.translation_siteaccesses' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.global.translation_siteaccesses' ) );
        $this->assertTrue( $this->container->hasParameter( 'ezsettings.ezdemo_site_admin.translation_siteaccesses' ) );
        $this->assertSame( $translationSAsDemoSite, $this->container->getParameter( 'ezsettings.ezdemo_site.translation_siteaccesses' ) );
        $this->assertSame( $translationSAsDemoSite, $this->container->getParameter( 'ezsettings.fre.translation_siteaccesses' ) );
        $this->assertEmpty( $this->container->getParameter( 'ezsettings.ezdemo_site_admin.translation_siteaccesses' ) );
    }
}
