<?php
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use PHPUnit_Framework_Assert as Assertion;

class ConsoleContext implements Context, SnippetAcceptingContext
{
    private $scriptOutput = null;

    /**
     * @When I run a console script without specifying a siteaccess
     */
    public function iRunAConsoleScriptWithoutSpecifyingASiteaccess()
    {
        $this->iRunTheCommand('ez:behat:siteaccess');
    }

    /**
     * @When I run a console script with the siteaccess option :siteaccessOption
     */
    public function iRunAConsoleScriptWithTheSiteaccessOption($siteaccessOption)
    {
        $this->iRunTheCommand('ez:behat:siteaccess', $siteaccessOption);
    }

    /**
     * @Then I expect it to be executed with the siteaccess :siteaccess
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
     * @Then I expect it to be executed with the default siteaccess
     */
    public function iExpectItToBeExecutedWithTheDefaultSiteaccess()
    {
        $this->iExpectItToBeExecutedWithTheSiteaccess('site');
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
        $console = escapeshellarg('ezpublish/console');
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
}
