<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\MinkExtension\Context\RawMinkContext;

class ExceptionContext extends RawMinkContext implements Context, SnippetAcceptingContext, MinkAwareContext
{
    /**
     * @Given /^that I am not logged in$/
     */
    public function iAmNotLoggedIn()
    {
        $this->visitPath('/logout');
    }

    /**
     * @Given /^that I am logged in$/
     */
    public function iAmLoggedIn()
    {
        $this->visitPath('/login');
        $this->getSession()->getPage()->fillField('Username', 'admin');
        $this->getSession()->getPage()->fillField('Password', 'publish');
        $this->getSession()->getPage()->pressButton('Login');
    }

    /**
     * @When /^a repository UnauthorizedException is thrown during an HTTP request$/
     */
    public function anExceptionIsThrownDuringAnHTTPRequest()
    {
        $this->visitPath('/platform-behat/exceptions/repository-unauthorized');
    }

    /**
     * @Then /^it is converted to a Symfony Security AccessDeniedException$/
     */
    public function itIsConvertedToAnSymfonyComponentSecurityCoreExceptionAccessDeniedException()
    {
        // unsure how to assert this :)
    }

    /**
     * @Given /^the login form is shown$/
     */
    public function theLoginFormIsShown()
    {
        $this->assertSession()->addressEquals('/login');
    }

    /**
     * @Then /^(?:a|an) ([\w\\]+Exception) is displayed$/
     */
    public function anAccessDeniedExceptionIsThrown($exceptionString)
    {
        $this->assertSession()->elementExists('css', "abbr[title='$exceptionString']");
    }
}
