<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use eZ\Publish\Core\Search\Common\Indexer;
use RuntimeException;

class ReindexCommand extends ContainerAwareCommand
{
    /**
     * @var \eZ\Publish\Core\Search\Common\Indexer
     */
    private $searchIndexer;

    /**
     * Initialize objects required by {@see execute()}.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->searchIndexer = $this->getContainer()->get('ezpublish.spi.search.indexer');
        if (!$this->searchIndexer instanceof Indexer) {
            throw new RuntimeException(
                sprintf('Expected to find Search Engine Indexer but found "%s" instead', get_parent_class($this->searchIndexer))
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
            ->addOption('iteration-count', 'c', InputOption::VALUE_OPTIONAL, 'Number of objects to be indexed in a single iteration', 20)
            ->addOption('no-commit', null, InputOption::VALUE_NONE, 'Do not commit after each iteration')
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
        $iterationCount = $input->getOption('iteration-count');
        $noCommit = $input->getOption('no-commit');

        if (!is_numeric($iterationCount) || (int)$iterationCount < 1) {
            throw new RuntimeException("'--iteration-count' option should be > 0, got '{$iterationCount}'");
        }

        $this->searchIndexer->createSearchIndex($output, intval($iterationCount), empty($noCommit));
    }
}
