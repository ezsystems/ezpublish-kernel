<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use EzSystems\BehatBundle\Context\Browser\Context as BrowserContext;
use PHPUnit_Framework_Assert as Assertion;

class ContentPreviewContext extends BrowserContext implements Context, SnippetAcceptingContext
{
    /**
     * Items referenced by key sentences.
     * @var array
     */
    private $_;

    /**
     * @Given /^I create an article draft$/
     */
    public function iCreateAnArticleDraft()
    {
        $draft = $this->createArticleDraft('Preview draft ' . date('c'));
        $this->_['this draft'] = $draft;
    }

    /**
     * That would be a blog post.
     *
     * @Given /^I create a draft for a content type that uses a custom location controller$/
     */
    public function iCreateDraftContentTypeWithCustomLocationController()
    {
        $fields = array(
            'title' => 'Preview draft ' . date('c'),
            'body' => '<?xml version="1.0" encoding="utf-8"?><section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph>This is a paragraph.</paragraph></section>',
        );
        $draft = $this->getBasicContentManager()->createContentDraft(2, 'blog_post', $fields);
        $this->_['this draft'] = $draft;
    }

    /**
     * @When /^I preview this draft$/
     */
    public function iPreviewThisDraft()
    {
        /** @var Content $content */
        $content = $this->_['this draft'];

        $this->visit($this->mapToVersionViewUri($content->versionInfo));
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
}
