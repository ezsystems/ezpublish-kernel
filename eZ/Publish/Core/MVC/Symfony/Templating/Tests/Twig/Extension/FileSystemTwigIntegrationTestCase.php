<?php

/**
 * File containing the FileSystemTwigIntegrationTestCase class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension;

use PHPUnit\Framework\Constraint\Exception as PHPUnitException;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\Test\IntegrationTestCase;
use Exception;

/**
 * Class FileSystemTwigIntegrationTestCase
 * This class adds a custom version of the doIntegrationTest from Twig_Test_IntegrationTestCase to
 * allow loading (custom) templates located in the FixturesDir.
 */
abstract class FileSystemTwigIntegrationTestCase extends IntegrationTestCase
{
    /**
     * Overrides the default implementation to use the chain loader so that
     * templates used internally are correctly loaded.
     */
    protected function doIntegrationTest($file, $message, $condition, $templates, $exception, $outputs, $deprecation = '')
    {
        if ($condition) {
            eval('$ret = ' . $condition . ';');
            if (!$ret) {
                $this->markTestSkipped($condition);
            }
        }

        // changes from the original is here, Twig_Loader_Filesystem has been added
        $loader = new ChainLoader(
            [
                new ArrayLoader($templates),
                new FilesystemLoader($this->getFixturesDir()),
            ]
        );
        // end changes

        foreach ($outputs as $match) {
            $config = array_merge(
                [
                    'cache' => false,
                    'strict_variables' => true,
                ],
                $match[2] ? eval($match[2] . ';') : []
            );
            $twig = new Environment($loader, $config);
            $twig->addGlobal('global', 'global');
            foreach ($this->getExtensions() as $extension) {
                $twig->addExtension($extension);
            }

            try {
                $template = $twig->loadTemplate('index.twig');
            } catch (Exception $e) {
                if (false !== $exception) {
                    $this->assertEquals(
                        trim($exception),
                        trim(
                            sprintf('%s: %s', get_class($e), $e->getMessage())
                        )
                    );

                    return;
                }

                if ($e instanceof SyntaxError) {
                    $e->setTemplateFile($file);

                    throw $e;
                }

                throw new Error(sprintf('%s: %s', get_class($e), $e->getMessage()), -1, $file, $e);
            }

            try {
                $output = trim($template->render(eval($match[1] . ';')), "\n ");
            } catch (Exception $e) {
                if (false !== $exception) {
                    $this->assertStringContainsString(
                        trim($exception),
                        trim(
                            sprintf('%s: %s', get_class($e), $e->getMessage())
                        )
                    );

                    return;
                }

                if ($e instanceof SyntaxError) {
                    $e->setTemplateFile($file);
                } else {
                    $e = new Error(sprintf('%s: %s', get_class($e), $e->getMessage()), -1, $file, $e);
                }

                $output = trim(
                    sprintf('%s: %s', get_class($e), $e->getMessage())
                );
            }

            if (false !== $exception) {
                list($class) = explode(':', $exception);
                $this->assertThat(
                    null,
                    new PHPUnitException($class)
                );
            }

            $expected = trim($match[3], "\n ");

            if ($expected != $output) {
                echo 'Compiled template that failed:';

                foreach (array_keys($templates) as $name) {
                    echo "Template: $name\n";
                    $source = $loader->getSourceContext($name);
                    echo $twig->compile(
                        $twig->parse($twig->tokenize($source))
                    );
                }
            }
            $this->assertEquals($expected, $output, $message . ' (in ' . $file . ')');
        }
    }
}
