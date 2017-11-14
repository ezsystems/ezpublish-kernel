<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Exception;

/**
 * Console Command which removes a given Translation from all the Versions of a given Content Object.
 */
class RemoveContentTranslationCommand extends Command
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    private $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @var \Symfony\Component\Console\Helper\QuestionHelper
     */
    private $questionHelper;

    public function __construct(Repository $repository)
    {
        parent::__construct(null);
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ezplatform:remove-content-translation')
            ->addArgument('content-id', InputArgument::REQUIRED, 'Content Object Id')
            ->addArgument(
                'language-code',
                InputArgument::REQUIRED,
                'Language code of the Translation to be removed'
            )
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_OPTIONAL,
                'eZ Platform username (with Role containing at least Content policies: read, versionread, edit, remove, versionremove)',
                'admin'
            )
            ->setDescription('Remove Translation from all the Versions of a Content Object');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->input = $input;
        $this->output = $output;
        $this->questionHelper = $this->getHelper('question');
        $this->contentService = $this->repository->getContentService();

        $this->repository->getPermissionResolver()->setCurrentUserReference(
            $this->repository->getUserService()->loadUserByLogin($input->getOption('user'))
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contentId = (int) ($input->getArgument('content-id'));
        $languageCode = $input->getArgument('language-code');

        if ($contentId === 0) {
            throw new InvalidArgumentException(
                'content-id',
                'Content Object Id has to be an integer'
            );
        }

        $this->output->writeln(
            '<comment>**NOTE**: Make sure to run this command using the same SYMFONY_ENV setting as your eZ Platform installation does</comment>'
        );

        $contentInfo = $this->contentService->loadContentInfo($contentId);

        $this->repository->beginTransaction();
        try {
            $allLanguages = $this->removeAffectedSingularLanguageVersions(
                $contentInfo,
                $languageCode
            );
            if ($contentInfo->mainLanguageCode === $languageCode) {
                $contentInfo = $this->promptUserForMainLanguageChange(
                    $contentInfo,
                    $languageCode,
                    $allLanguages
                );
            }

            // Confirm operation
            $contentName = "#{$contentInfo->id} ($contentInfo->name)";
            $question = new ConfirmationQuestion(
                "Are you sure you want to remove {$languageCode} Translation from the Content {$contentName}? This operation is permanent. [y/N] ",
                false
            );
            if (!$this->questionHelper->ask($this->input, $this->output, $question)) {
                // Rollback any cleanup change (see above)
                $this->repository->rollback();
                $this->output->writeln('Reverting and aborting.');

                return;
            }

            // Remove Translation
            $output->writeln(
                "<info>Removing {$languageCode} Translation of the Content {$contentName}</info>"
            );
            $this->contentService->removeTranslation($contentInfo, $languageCode);

            $output->writeln('<info>Translation removed</info>');

            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Cleanup Versions before removing Translation and collect existing Translations languages.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string $languageCode
     *
     * @return string[] unique Language codes across all Versions of the Content.
     */
    private function removeAffectedSingularLanguageVersions(ContentInfo $contentInfo, $languageCode)
    {
        $languages = [];
        foreach ($this->contentService->loadVersions($contentInfo) as $versionInfo) {
            // if this is the only one Translation, just delete entire Version
            if (count($versionInfo->languageCodes) === 1 && $versionInfo->languageCodes[0] === $languageCode) {
                // Note: won't work on published Versions and last remaining Version
                $this->contentService->deleteVersion($versionInfo);
                continue;
            }

            foreach ($versionInfo->languageCodes as $lang) {
                if ($lang === $languageCode || in_array($lang, $languages)) {
                    continue;
                }
                $languages[] = $lang;
            }
        }

        return $languages;
    }

    /**
     * Interact with user to update main Language of a Content Object.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string $languageCode language code of the Translation to be removed
     * @param string[] $allLanguages all languages Content Object Versions have, w/o $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    private function promptUserForMainLanguageChange(
        ContentInfo $contentInfo,
        $languageCode,
        array $allLanguages
    ) {
        $contentName = "#{$contentInfo->id} ($contentInfo->name)";
        $this->output->writeln(
            "<comment>The specified language '{$languageCode}' is the main language of the Content {$contentName}. It needs to be changed before removal.</comment>"
        );

        $question = new ChoiceQuestion(
            "Set the main language of the Content {$contentName} to:",
            $allLanguages
        );

        $newMainLanguageCode = $this->questionHelper->ask($this->input, $this->output, $question);
        $this->output->writeln(
            "<info>Updating Main Language of the Content {$contentName} to {$newMainLanguageCode}</info>"
        );

        $contentMetadataUpdateStruct = $this->contentService->newContentMetadataUpdateStruct();
        $contentMetadataUpdateStruct->mainLanguageCode = $newMainLanguageCode;

        return $this->contentService->updateContentMetadata(
            $contentInfo,
            $contentMetadataUpdateStruct
        )->contentInfo;
    }
}
