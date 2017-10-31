<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use EzSystems\BehatBundle\Context\Browser\Context as BrowserContext;
use EzSystems\PlatformBehatBundle\Context\SubContext\DeprecationNoticeSupressor;
use PHPUnit\Framework\Assert as Assertion;

class ContentPreviewContext extends BrowserContext implements Context, SnippetAcceptingContext
{
    use DeprecationNoticeSupressor;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Features\Context\ContentContext */
    private $contentContext;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->contentContext = $environment->getContext('eZ\Bundle\EzPublishCoreBundle\Features\Context\ContentContext');
    }

    /**
     * @Given /^I create a draft for a content type that uses a custom location controller$/
     */
    public function iCreateDraftOfContentTypeWithCustomLocationController()
    {
        $this->contentContext->createDraft(
            'blog_post',
            [
                'title' => 'Preview draft ' . date('c'),
                'body' => '<?xml version="1.0" encoding="UTF-8"?><section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0"><para>This is a paragraph.</para></section>',
            ]
        );
    }

    /**
     * @When /^I preview this draft$/
     */
    public function iPreviewThisDraft()
    {
        $this->visit($this->mapToVersionViewUri($this->contentContext->getCurrentDraft()->versionInfo));
    }

    /**
     * @return string
     */
    private function mapToVersionViewUri(VersionInfo $version)
    {
        return sprintf(
            '/content/versionview/%s/%s/%s',
            $version->contentInfo->id,
            $version->versionNo,
            $version->initialLanguageCode
        );
    }

    /**
     * @Then /^the output is valid$/
     */
    public function theOutputIsValid()
    {
        $this->checkForExceptions();
    }

    protected function checkForExceptions()
    {
        $exceptionElements = $this->getXpath()->findXpath("//div[@class='text-exception']/h1");
        $exceptionStackTraceItems = $this->getXpath()->findXpath("//ol[@id='traces-0']/li");
        if (count($exceptionElements) > 0) {
            $exceptionElement = $exceptionElements[0];
            $exceptionLines = [$exceptionElement->getText(), ''];

            foreach ($exceptionStackTraceItems as $stackTraceItem) {
                $html = $stackTraceItem->getHtml();
                $html = substr($html, 0, strpos($html, '<a href', 1));
                $html = htmlspecialchars_decode(strip_tags($html));
                $html = preg_replace('/\s+/', ' ', $html);
                $html = str_replace('  (', '(', $html);
                $html = str_replace(' ->', '->', $html);
                $exceptionLines[] = trim($html);
            }
            $message = 'An exception occured during rendering:' . implode("\n", $exceptionLines);
            Assertion::assertTrue(false, $message);
        }
    }

    /**
     * @Then /^I see a preview of this draft$/
     */
    public function iSeeAPreviewOfTheCurrentDraft()
    {
        $this->assertSession()->elementContains(
            'xpath',
            "//span[@class='ezstring-field']",
            $this->contentContext->getCurrentDraft()->getFieldValue('name')->text
        );
    }

    /**
     * This could belong in the content context.
     *
     * @Given /^I modify a field from the draft$/
     */
    public function iModifyAFieldFromTheDraft()
    {
        $this->contentContext->updateDraft(
            ['name' => 'MODIFIED - ' . $this->contentContext->getCurrentDraft()->getFieldValue('name')->text]
        );
    }
}
