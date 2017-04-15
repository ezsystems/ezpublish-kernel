<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\Yaml\Yaml;

/**
 * Adds extra YAML configuration through ezplatform_behat.yml.
 *
 * New configuration blocks are added to unique files, and added to the imports.
 * Existing configuration strings re-use the same file if applicable.
 */
class YamlConfigurationContext implements Context
{
    private static $platformConfigurationFilePath = 'app/config/ezplatform_behat.yml';

    public function addConfiguration(array $configuration)
    {
        $yamlString = Yaml::dump($configuration, 5, 4);
        $destinationFileName = 'ezplatform_behat_' . sha1($yamlString) . '.yml';
        $destinationFilePath = 'app/config/' . $destinationFileName;

        if (!file_exists($destinationFilePath)) {
            file_put_contents($destinationFilePath, $yamlString);
        }

        $this->addImportToPlatformYaml($destinationFileName);

        shell_exec('php bin/console --env=behat cache:clear');
    }

    private function addImportToPlatformYaml($importedFileName)
    {
        $platformConfig = Yaml::parse(file_get_contents(self::$platformConfigurationFilePath));

        foreach ($platformConfig['imports'] as $import) {
            if ($import['resource'] == $importedFileName) {
                $importAlreadyExists = true;
            }
        }

        if (!isset($importAlreadyExists)) {
            $platformConfig['imports'][] = ['resource' => $importedFileName];

            file_put_contents(
                self::$platformConfigurationFilePath,
                Yaml::dump($platformConfig, 5, 4)
            );
        }
    }
}
