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
use PHPUnit\Framework\Assert;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class QueryControllerContext extends RawMinkContext implements Context
{
    /** @var YamlConfigurationContext */
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
                'template' => '@eZBehat/tests/dump.html.twig',
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
     * @Given /^the following content view configuration block with paging action:$/
     */
    public function addContentViewConfigurationBlockWithPagingAction(PyStringNode $string)
    {
        $configurationBlock = array_merge(
            Yaml::parse($string),
            [
                'template' => '@eZBehat/tests/dump.html.twig',
                'match' => [
                    'Id\Content' => $this->matchedContent->id,
                ],
            ]
        );

        $configurationBlockName = 'behat_paging_query_controller_' . $this->matchedContent->id;

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
        $this->matchedContent = $this->repository->sudo(function (Repository $repository) {
            return $this->createFolder($repository);
        });
    }

    /**
     * @Given :arg1 contents are created to test paging
     */
    public function contentsAreCreatedToTestPaging2($numberOfContents)
    {
        for ($i = 0; $i < $numberOfContents; ++$i) {
            $this->repository->sudo(function (Repository $repository) {
                return $this->createFolder($repository);
            });
        }
    }

    /**
     * @return Content
     */
    private function createFolder(Repository $repository)
    {
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        $struct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier('folder'),
            'eng-GB'
        );

        $struct->setField('name', uniqid('Query Controller BDD ', true));

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
        $fs->mkdir(\dirname($phpFilePath));
        $fs->dumpFile($phpFilePath, $phpFileContents);
        shell_exec('php bin/console --env=behat cache:clear');
    }

    /**
     * @When /^I view a content matched by the view configuration above$/
     */
    public function visitMatchedContent()
    {
        $urlAliasService = $this->repository->getURLAliasService();
        $urlAlias = $urlAliasService->reverseLookup(
            $this->repository->getLocationService()->loadLocation(
                $this->matchedContent->contentInfo->mainLocationId
            )
        );

        $this->visitPath($urlAlias->path);

        if ($this->getSession()->getStatusCode() !== 200) {
            $page = $this->getSession()->getPage();
            $exceptionElements = $page->findAll('xpath', "//div[@class='text-exception']/h1");
            $exceptionStackTraceItems = $page->findAll('xpath', "//ol[@id='traces-0']/li");
            if (\count($exceptionElements) > 0) {
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
                Assert::assertTrue(false, $message);
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
        $variableTypes = $this->getVariableTypesFromTemplate();

        Assert::assertArrayHasKey($twigVariableName, $variableTypes, "The $twigVariableName twig variable was not set");
    }

    /**
     * @Then the Query results assigned to the :arg1 twig variable is a :arg2 object
     */
    public function theQueryResultsAssignedToTheTwigVariableIsAObject($twigVariableName, $className)
    {
        $variableTypes = $this->getVariableTypesFromTemplate();

        Assert::assertArrayHasKey($twigVariableName, $variableTypes, "The $twigVariableName twig variable was not set");
        Assert::assertEquals($className, $variableTypes[$twigVariableName], "The $twigVariableName twig variable does not have $className type");
    }

    /**
     * @Given /^the following template defined in "([^"]*)":$/
     */
    public function createTemplateFile($tplFilePath, PyStringNode $tplFileContents)
    {
        $fs = new Filesystem();
        $fs->mkdir(\dirname($tplFilePath));
        $fs->dumpFile($tplFilePath, $tplFileContents);
    }

    /**
     * @Given the following content view configuration block with paging action and the template set above:
     */
    public function theFollowingContentViewConfigurationBlockWithPagingActionAndTheTemplateSetAbove(PyStringNode $string)
    {
        $configurationBlock = array_merge(
            Yaml::parse($string),
            [
                'match' => [
                    'Id\Content' => $this->matchedContent->id,
                ],
            ]
        );

        $configurationBlockName = 'behat_paging_query_controller_' . $this->matchedContent->id;

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
     * @When I view a content matched by the view configuration above on page :arg1 with the :arg2 parameter
     */
    public function iViewAContentMatchedByTheViewConfigurationAboveOnPageWithTheParameter($pageNumber, $pageParam)
    {
        $urlAliasService = $this->repository->getURLAliasService();
        $urlAlias = $urlAliasService->reverseLookup(
            $this->repository->getLocationService()->loadLocation(
                $this->matchedContent->contentInfo->mainLocationId
            )
        );

        $this->visitPath($urlAlias->path . "?$pageParam=$pageNumber");

        if ($this->getSession()->getStatusCode() !== 200) {
            $page = $this->getSession()->getPage();
            $exceptionElements = $page->findAll('xpath', "//div[@class='text-exception']/h1");
            $exceptionStackTraceItems = $page->findAll('xpath', "//ol[@id='traces-0']/li");
            if (\count($exceptionElements) > 0) {
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
                Assert::assertTrue(false, $message);
            }
        }
        $this->assertSession()->statusCodeEquals(200);
    }

    /**
     * @Then the Query results assigned to the twig variable is a Pagerfanta object and has limit :arg1 and selected page :arg2
     */
    public function theQueryResultsAssignedToTheTwigVariableIsAObjectAndHasLimitAndCountParams($pageLimit, $pageValue)
    {
        $pageLimitFound = false;
        $currentPageFound = false;

        $page = $this->getSession()->getPage();
        $maxPerPage = $page->findAll('css', 'div#maxPerPage');
        $currentPage = $page->findAll('css', 'div#currentPage');

        /** @var NodeElement $variableNode */
        foreach ($maxPerPage as $variableNode) {
            if ($variableNode->getText() === $pageLimit) {
                $pageLimitFound = true;
            }
        }

        /** @var NodeElement $valueNodes */
        foreach ($currentPage as $valueNode) {
            if ($valueNode->getText() === $pageValue) {
                $currentPageFound = true;
            }
        }

        Assert::assertTrue(
            $pageLimitFound,
            "The maxPerPage $pageLimit twig variable was not set"
        );

        Assert::assertTrue(
            $currentPageFound,
            "The currentPage $pageValue twig variable  was not set"
        );
    }

    /**
     * Returns an associative array with Twig variables as keys and their types as values.
     *
     * @return array
     */
    private function getVariableTypesFromTemplate(): array
    {
        $variableRows = $this->getSession()->getPage()->findAll('css', '.dump .item');

        $items = [];

        foreach ($variableRows as $row) {
            $variable = $row->find('css', '.variable')->getText();
            $type = $row->find('css', '.type')->getText();

            $items[$variable] = $type;
        }

        return $items;
    }
}
