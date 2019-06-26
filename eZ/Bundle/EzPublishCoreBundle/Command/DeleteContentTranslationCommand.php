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
 * Console Command which deletes a given Translation from all the Versions of a given Content Item.
 */
class DeleteContentTranslationCommand extends Command
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \Symfony\Component\Console\Input\InputInterface */
    private $input;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    private $output;

    /** @var \Symfony\Component\Console\Helper\QuestionHelper */
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
            ->setName('ezplatform:delete-content-translation')
            ->addArgument('content-id', InputArgument::REQUIRED, 'Content Object Id')
            ->addArgument(
                'language-code',
                InputArgument::REQUIRED,
                'Language code of the Translation to be deleted'
            )
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_OPTIONAL,
                'eZ Platform username (with Role containing at least Content policies: read, versionread, edit, remove, versionremove)',
                'admin'
            )
            ->setDescription('Delete Translation from all the Versions of a Content Item');
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

        $versionInfo = $this->contentService->loadVersionInfoById($contentId);
        $contentInfo = $versionInfo->contentInfo;

        $this->repository->beginTransaction();
        try {
            if ($contentInfo->mainLanguageCode === $languageCode) {
                $contentInfo = $this->promptUserForMainLanguageChange(
                    $contentInfo,
                    $languageCode,
                    // allow to change Main Translation to only those existing in the last Version
                    $versionInfo->languageCodes
                );
            }

            // Confirm operation
            $contentName = "#{$contentInfo->id} ($contentInfo->name)";
            $question = new ConfirmationQuestion(
                "Are you sure you want to delete {$languageCode} Translation from the Content {$contentName}? This operation is permanent. [y/N] ",
                false
            );
            if (!$this->questionHelper->ask($this->input, $this->output, $question)) {
                // Rollback any cleanup change (see above)
                $this->repository->rollback();
                $this->output->writeln('Reverting and aborting.');

                return;
            }

            // Delete Translation
            $output->writeln(
                "<info>Deleting {$languageCode} Translation of the Content {$contentName}</info>"
            );
            $this->contentService->deleteTranslation($contentInfo, $languageCode);

            $output->writeln('<info>Translation deleted</info>');

            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Interact with user to update main Language of a Content Object.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string $languageCode language code of the Translation to be deleted
     * @param string[] $lastVersionLanguageCodes all Translations last Version has.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    private function promptUserForMainLanguageChange(
        ContentInfo $contentInfo,
        $languageCode,
        array $lastVersionLanguageCodes
    ) {
        $contentName = "#{$contentInfo->id} ($contentInfo->name)";
        $this->output->writeln(
            "<comment>The specified Translation '{$languageCode}' is the Main Translation of the Content {$contentName}. It needs to be changed before removal.</comment>"
        );

        // get main Translation candidates w/o Translation being removed
        $mainTranslationCandidates = array_filter(
            $lastVersionLanguageCodes,
            function ($versionLanguageCode) use ($languageCode) {
                return $versionLanguageCode !== $languageCode;
            }
        );
        if (empty($mainTranslationCandidates)) {
            throw new InvalidArgumentException(
                'language-code',
                "The last Version of the Content {$contentName} has no other Translations beside the main one"
            );
        }
        $question = new ChoiceQuestion(
            "Set the Main Translation of the Content {$contentName} to:",
            array_values($mainTranslationCandidates)
        );

        $newMainLanguageCode = $this->questionHelper->ask($this->input, $this->output, $question);
        $this->output->writeln(
            "<info>Updating Main Translation of the Content {$contentName} to {$newMainLanguageCode}</info>"
        );

        $contentMetadataUpdateStruct = $this->contentService->newContentMetadataUpdateStruct();
        $contentMetadataUpdateStruct->mainLanguageCode = $newMainLanguageCode;

        return $this->contentService->updateContentMetadata(
            $contentInfo,
            $contentMetadataUpdateStruct
        )->contentInfo;
    }
}
