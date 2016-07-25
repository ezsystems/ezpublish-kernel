<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use eZ\Publish\SPI\Search\Indexing;
use RuntimeException;

class ReindexCommand extends ContainerAwareCommand
{
    /**
     * @var \eZ\Publish\SPI\Search\Indexing
     */
    private $searchHandler;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \eZ\Publish\SPI\Search\IndexerDataProvider
     */
    private $dataProvider;

    /**
     * Initialize objects required by {@see execute()}.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->logger = $this->getContainer()->get('logger');
        $this->searchHandler = $this->getContainer()->get('ezpublish.spi.search');
        $this->dataProvider = $this->getContainer()->get('ezpublish.search.common.indexer.data_provider');
        if (!$this->searchHandler instanceof Indexing) {
            throw new RuntimeException(
                'Expected to find Search Engine Handler capable of Indexing but found something else.'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ezplatform:reindex')
            ->setDescription('Recreate search engine index')
            ->addArgument('bulk_count', InputArgument::OPTIONAL, 'Number of objects to be indexed at once', 5)
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> indexes current configured database in configured search engine index.
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bulkCount = $input->getArgument('bulk_count');

        $output->writeln('Creating search index for the engine: ' . get_parent_class($this->searchHandler));

        $this->searchHandler->createSearchIndex(
            $bulkCount,
            $this->dataProvider,
            $output,
            $this->logger
        );

        $output->writeln(PHP_EOL . 'Finished creating search index for the engine: ' . get_parent_class($this->searchHandler));
    }
}
