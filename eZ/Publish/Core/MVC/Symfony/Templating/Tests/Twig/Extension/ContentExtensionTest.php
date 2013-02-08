<?php
/**
 * File containing the ContentExtensionIntegrationTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension;

use eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension\ContentExtension;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use Twig_Test_IntegrationTestCase;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_Loader_Chain;
use Twig_Loader_Array;

class ContentExtensionIntegrationTest extends Twig_Test_IntegrationTestCase
{

    public function getExtensions()
    {
        return array(
            new ContentExtension(
                $this->getContainerMock(),
                $this->getConfigResolverMock()
            )
        );
    }

    /**
     * Overrides the default implementation to use the chain loader so that
     * templates used internally by ez_render_* are correctly loaded
     */
    protected function doIntegrationTest($file, $message, $condition, $templates, $exception, $outputs)
    {
        if ($condition) {
            eval('$ret = '.$condition.';');
            if (!$ret) {
                $this->markTestSkipped($condition);
            }
        }

        // changes from the original is here
        $loader = new Twig_Loader_Chain(
            array(
                new Twig_Loader_Array( $templates ),
                new Twig_Loader_Filesystem( $this->getFixturesDir() )
            )
        );
        // end changes

        foreach ($outputs as $match) {
            $config = array_merge(array(
                'cache' => false,
                'strict_variables' => true,
            ), $match[2] ? eval($match[2].';') : array());
            $twig = new Twig_Environment($loader, $config);
            $twig->addGlobal('global', 'global');
            foreach ($this->getExtensions() as $extension) {
                $twig->addExtension($extension);
            }

            try {
                $template = $twig->loadTemplate('index.twig');
            } catch (Exception $e) {
                if (false !== $exception) {
                    $this->assertEquals(trim($exception), trim(sprintf('%s: %s', get_class($e), $e->getMessage())));

                    return;
                }

                if ($e instanceof Twig_Error_Syntax) {
                    $e->setTemplateFile($file);

                    throw $e;
                }

                throw new Twig_Error(sprintf('%s: %s', get_class($e), $e->getMessage()), -1, $file, $e);
            }

            try {
                $output = trim($template->render(eval($match[1].';')), "\n ");
            } catch (Exception $e) {
                if (false !== $exception) {
                    $this->assertEquals(trim($exception), trim(sprintf('%s: %s', get_class($e), $e->getMessage())));

                    return;
                }

                if ($e instanceof Twig_Error_Syntax) {
                    $e->setTemplateFile($file);
                } else {
                    $e = new Twig_Error(sprintf('%s: %s', get_class($e), $e->getMessage()), -1, $file, $e);
                }

                $output = trim(sprintf('%s: %s', get_class($e), $e->getMessage()));
            }

            if (false !== $exception) {
                list($class, ) = explode(':', $exception);
                $this->assertThat(NULL, new PHPUnit_Framework_Constraint_Exception($class));
            }

            $expected = trim($match[3], "\n ");

            if ($expected != $output) {
                echo 'Compiled template that failed:';

                foreach (array_keys($templates) as $name) {
                    echo "Template: $name\n";
                    $source = $loader->getSource($name);
                    echo $twig->compile($twig->parse($twig->tokenize($source, $name)));
                }
            }
            $this->assertEquals($expected, $output, $message.' (in '.$file.')');
        }
    }


    public function getFixturesDir()
    {
        return dirname( __FILE__ ) . '/_fixtures/';
    }

    public function getFieldDefinition( $typeIdentifier, $id = null, $settings = array() )
    {
        return new FieldDefinition(
            array(
                'id' => $id,
                'fieldSettings' => $settings,
                'fieldTypeIdentifier' => $typeIdentifier
            )
        );
    }

    protected function getContent( $contentTypeIdentifier, $fieldsInfo )
    {
        $fields = array();
        $fieldDefinitions = array();
        foreach ( $fieldsInfo as $type => $info )
        {
            $fields[] = new Field( $info );
            $fieldDefinitions[] = new FieldDefinition(
                array(
                    'identifier' => $info['fieldDefIdentifier'],
                    'id' => $info['id'],
                    'fieldTypeIdentifier' => $type,
                )
            );
        }
        $content = new Content(
            array(
                'internalFields' => $fields,
                'versionInfo' => new VersionInfo(
                    array(
                        'versionNo' => 64,
                        'contentInfo' => new ContentInfo(
                            array(
                                'id' => 42,
                                'mainLanguageCode' => 'fre-FR',
                                'contentType' => new ContentType(
                                    array(
                                        'identifier' => $contentTypeIdentifier,
                                        'fieldDefinitions' => $fieldDefinitions
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );

        return $content;

    }

    private function getTemplatePath( $tpl )
    {
        return 'templates/' . $tpl;
    }

    private function getConfigResolverMock()
    {
        $mock = $this->getMock(
            'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface'
        );
        $mock->expects( $this->any() )
            ->method( 'getParameter' )
            ->will(
                $this->returnValueMap(
                    array(
                        array(
                            'field_templates',
                            null,
                            null,
                            array(
                                array(
                                    'template' => $this->getTemplatePath( 'fields_override1.html.twig' ),
                                    'priority' => 10
                                ),
                                array(
                                    'template' => $this->getTemplatePath( 'fields_default.html.twig' ),
                                    'priority' => 0
                                ),
                                array(
                                    'template' => $this->getTemplatePath( 'fields_override2.html.twig' ),
                                    'priority' => 20
                                ),
                            )
                        ),
                        array(
                            'fielddefinition_settings_templates',
                            null,
                            null,
                            array(
                                array(
                                    'template' => $this->getTemplatePath( 'settings_override1.html.twig' ),
                                    'priority' => 10
                                ),
                                array(
                                    'template' => $this->getTemplatePath( 'settings_default.html.twig' ),
                                    'priority' => 0
                                ),
                                array(
                                    'template' => $this->getTemplatePath( 'settings_override2.html.twig' ),
                                    'priority' => 20
                                ),
                            )
                        )
                    )
                )
            );
        return $mock;
    }

    private function getContainerMock()
    {
        $mock = $this->getMock(
            'Symfony\\Component\\DependencyInjection\\ContainerInterface'
        );
        return $mock;
    }


}
