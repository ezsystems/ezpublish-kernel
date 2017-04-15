<?php
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\BehatBundle\Context\EzContext;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use PHPUnit_Framework_Assert as Assertion;

class ConsoleContext extends EzContext implements Context, SnippetAcceptingContext
{
    private $scriptOutput = null;

    private $nonDefaultSiteaccessName = null;

    /**
     * Elements referenced by 'it' in sentences.
     * @var array
     */
    private $it = [];

    /**
     * @When I run a console script without specifying a siteaccess
     */
    public function iRunAConsoleScript()
    {
        $this->iRunTheCommand('ez:behat:siteaccess');
    }

    /**
     * @When I run a console script with the siteaccess option :siteaccessOption
     */
    public function iRunAConsoleScriptWithSiteaccess($siteaccessOption)
    {
        $this->iRunTheCommand('ez:behat:siteaccess', $siteaccessOption);
    }

    /**
     * @Then It is executed with the siteaccess :siteaccess
     */
    public function iExpectItToBeExecutedWithTheSiteaccess($siteaccess)
    {
        $actualSiteaccess = trim($this->scriptOutput);
        Assertion::assertEquals(
            $siteaccess,
            $actualSiteaccess,
            "The command was expected to be executed with the siteaccess \"$siteaccess\", but was executed with \"$actualSiteaccess\""
        );
    }

    /**
     * @Then it is executed with the default one
     *
     * default one: default siteaccess.
     */
    public function iExpectItToBeExecutedWithTheDefaultOne()
    {
        $this->iExpectItToBeExecutedWithTheSiteaccess($this->getDefaultSiteaccessName());
    }

    /**
     * @Given /^that there is a "([^"]*)" siteaccess$/
     */
    public function thereIsASiteaccess($expectedSiteaccessName, $default = false)
    {
        $found = false;

        $siteaccessList = $this->getConfigResolver()->getParameter('siteaccess.list');
        foreach ($siteaccessList as $siteaccessName) {
            if ($siteaccessName === $expectedSiteaccessName) {
                $found = $default === false || $siteaccessName !== $this->getDefaultSiteaccessName();
            }
        }

        Assertion::assertTrue($found, "No siteaccess named $expectedSiteaccessName was found");
        $this->it['siteaccess'] = $expectedSiteaccessName;
    }

    /**
     * @Given /^that there is a default "([^"]*)" siteaccess$/
     */
    public function thereIsADefaultSiteaccess($expectedSiteaccessName)
    {
        $this->thereIsASiteaccess($expectedSiteaccessName, true);
        Assertion::assertEquals(
            $expectedSiteaccessName,
            $siteaccessList = $this->getConfigResolver()->getParameter('siteaccess.default_siteaccess')
        );
    }

    /**
     * @When I run a console script with it
     *
     * it: the siteaccess referenced above.
     */
    public function iRunAConsoleScriptWithIt()
    {
        $this->iRunTheCommand(
            'ez:behat:siteaccess',
            $this->it['siteaccess']
        );
        $this->it['siteaccess'] = $this->scriptOutput;
    }

    private function iRunTheCommand($command, $siteaccess = null)
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find(false)) {
            throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
        }
        $arguments = $phpFinder->findArguments();
        if (false !== ($ini = php_ini_loaded_file())) {
            $arguments[] = '--php-ini=' . $ini;
        }
        $php = escapeshellarg($phpPath);
        $phpArgs = implode(' ', array_map('escapeshellarg', $arguments));
        $console = escapeshellarg('bin/console');
        $cmd = escapeshellarg($command);

        $console .= ' --env=' . escapeshellarg('behat');
        if ($siteaccess !== null) {
            $console .= ' --siteaccess=' . escapeshellarg($siteaccess);
        }

        $commandLine = $php . ($phpArgs ? ' ' . $phpArgs : '') . ' ' . $console . ' ' . $cmd;
        $process = new Process($commandLine);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf('An error occurred when executing the "%s" command.', escapeshellarg($cmd)));
        }

        $this->scriptOutput = $process->getOutput();
    }

    /**
     * @Given /^that there is a siteaccess that is not the default one$/
     */
    public function thereIsASiteaccessThatIsNotTheDefaultOne()
    {
        $siteaccessName = $this->getNonDefaultSiteaccessName();
        Assertion::assertNotNull($siteaccessName, 'There is no siteaccess other than the default one');
        $this->it['siteaccess'] = $siteaccessName;
    }

    /**
     * @Then /^I expect it to be executed with it$/
     */
    public function iExpectItToBeExecutedWithIt()
    {
        Assertion::assertEquals($this->it['siteaccess'], $this->scriptOutput);
    }

    /**
     * Returns the name of an existing siteaccess that isn't the default one.
     * @return string|null The siteaccess name, or null if there isn't one
     */
    private function getNonDefaultSiteaccessName()
    {
        $defaultSiteaccessName = $this->getDefaultSiteaccessName();
        foreach ($this->getKernel()->getContainer()->getParameter('ezpublish.siteaccess.list') as $siteaccessName) {
            if ($siteaccessName !== $defaultSiteaccessName) {
                return $siteaccessName;
            }
        }

        return null;
    }

    /**
     * @return ConfigResolverInterface
     */
    private function getConfigResolver()
    {
        return $this->getKernel()->getContainer()->get('ezpublish.config.resolver');
    }

    private function getDefaultSiteaccessName()
    {
        return $this->getKernel()->getContainer()->getParameter('ezpublish.siteaccess.default');
    }
}
