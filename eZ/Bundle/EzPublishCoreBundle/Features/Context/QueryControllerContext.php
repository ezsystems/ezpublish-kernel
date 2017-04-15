<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Element\NodeElement;
use Behat\MinkExtension\Context\RawMinkContext;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use EzSystems\PlatformBehatBundle\Context\RepositoryContext;
use PHPUnit_Framework_Assert;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class QueryControllerContext extends RawMinkContext implements Context
{
    use RepositoryContext;

    /**
     * @var YamlConfigurationContext
     */
    private $configurationContext;

    /**
     * Content item matched by the view configuration.
     * @var Content
     */
    private $matchedContent;

    /**
     * QueryControllerContext constructor.
     * @injectService $repository @ezpublish.api.repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->configurationContext = $environment->getContext(
            'eZ\Bundle\EzPublishCoreBundle\Features\Context\YamlConfigurationContext'
        );
    }

    /**
     * @Given /^the following content view configuration block:$/
     */
    public function addContentViewConfigurationBlock(PyStringNode $string)
    {
        $configurationBlock = array_merge(
            Yaml::parse($string),
            [
                'template' => 'EzPlatformBehatBundle::dump.html.twig',
                'match' => [
                    'Id\Content' => $this->matchedContent->id,
                ],
            ]
        );

        $configurationBlockName = 'behat_query_controller_' . $this->matchedContent->id;

        $configuration = [
            'ezpublish' => [
                'system' => [
                    'default' => [
                        'content_view' => [
                            'full' => [
                                $configurationBlockName => $configurationBlock,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->configurationContext->addConfiguration($configuration);
    }

    /**
     * @Given /^a content item that matches the view configuration block below$/
     */
    public function aContentItemThatMatchesTheViewConfigurationBlockBelow()
    {
        $this->matchedContent = $this->createFolder();
    }

    /**
     * @return Content
     */
    private function createFolder()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        $struct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier('folder'),
            'eng-GB'
        );

        $struct->setField('name', uniqid('Query Controller BDD '));

        $contentDraft = $contentService->createContent(
            $struct,
            [$locationService->newLocationCreateStruct(2)]
        );
        $contentService->publishVersion($contentDraft->versionInfo);

        return $contentService->loadContent($contentDraft->id);
    }

    /**
     * @Given /^a LocationChildren QueryType defined in "([^"]*)":$/
     */
    public function createPhpFile($phpFilePath, PyStringNode $phpFileContents)
    {
        $fs = new Filesystem();
        $fs->mkdir(dirname($phpFilePath));
        $fs->dumpFile($phpFilePath, $phpFileContents);
        shell_exec('php bin/console --env=behat cache:clear');
    }

    /**
     * @When /^I view a content matched by the view configuration above$/
     */
    public function visitMatchedContent()
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $urlAlias = $urlAliasService->reverseLookup(
            $this->getRepository()->getLocationService()->loadLocation(
                $this->matchedContent->contentInfo->mainLocationId
            )
        );

        $this->visitPath($urlAlias->path);

        if ($this->getSession()->getStatusCode() !== 200) {
            $page = $this->getSession()->getPage();
            $exceptionElements = $page->findAll('xpath', "//div[@class='text-exception']/h1");
            $exceptionStackTraceItems = $page->findAll('xpath', "//ol[@id='traces-0']/li");
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
                $message = 'An exception occurred during rendering:' . implode("\n", $exceptionLines);
                PHPUnit_Framework_Assert::assertTrue(false, $message);
            }
        }
        $this->assertSession()->statusCodeEquals(200);
    }

    /**
     * @Then /^the viewed content's main location id is mapped to the parentLocationId QueryType parameter$/
     */
    public function theViewedContentSMainLocationIdIsMappedToTheParentLocationIdQueryTypeParameter()
    {
        // not sure how to assert that
    }

    /**
     * @Then /^a LocationChildren Query is built from the LocationChildren QueryType$/
     */
    public function aLocationChildrenQueryIsBuiltFromTheLocationChildrenQueryType()
    {
        // not sure how to assert that either
    }

    /**
     * @Given /^a Location Search is executed with the LocationChildren Query$/
     */
    public function aLocationSearchIsExecutedWithTheLocationChildrenQuery()
    {
        // still not sure...
    }

    /**
     * @Given /^the Query results are assigned to the "([^"]*)" twig variable$/
     */
    public function theQueryResultsAreAssignedToTheTwigVariable($twigVariableName)
    {
        $variableFound = false;

        $page = $this->getSession()->getPage();
        $variableNodes = $page->findAll('css', 'pre.sf-dump > samp > span.sf-dump-key');

        /** @var NodeElement $variableNode */
        foreach ($variableNodes as $variableNode) {
            if ($variableNode->getText() === $twigVariableName) {
                $variableFound = true;
            }
        }

        PHPUnit_Framework_Assert::assertTrue(
            $variableFound,
            "The $twigVariableName twig variable was not set"
        );
    }
}
