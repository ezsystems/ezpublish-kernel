<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved
 * @license For full copyright and license information view LICENSE file distributed with this source code
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Connection;
use RuntimeException;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\PhpExecutableFinder;
use PDO;

class UpdateTimestampsToUTCCommand extends ContainerAwareCommand
{
    const DEFAULT_ITERATION_COUNT = 100;

    /**
     * @var int
     */
    protected $done = 0;

    /**
     * @var string
     */
    protected $timezone;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $phpPath;

    /**
     * @var bool
     */
    private $dryRun;

    protected function configure()
    {
        $this
            ->setName('ezplatform:timestamps:to-utc')
            ->setDescription('Updates ezdate & ezdatetime timestamps to UTC')
            ->addArgument(
                'timezone',
                InputArgument::OPTIONAL,
                'Original timestamp TimeZone',
                null
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Execute a dry run'
            )
            ->addOption(
                'mode',
                null,
                InputOption::VALUE_REQUIRED,
                'Select conversion scope: date, datetime, all',
                'all'
            )
            ->addOption(
                'only-datetime',
                null,
                InputOption::VALUE_NONE,
                'Only ezdatetime fields will be converted'
            )
            ->addOption(
                'from',
                null,
                InputOption::VALUE_REQUIRED,
                'Only versions AFTER this date will be converted',
                null
            )
            ->addOption(
                'to',
                null,
                InputOption::VALUE_REQUIRED,
                'Only versions BEFORE this date will be converted',
                null
            )
            ->addOption(
                'offset',
                null,
                InputArgument::OPTIONAL,
                'Offset for updating records',
                0
            )
            ->addOption(
                'iteration-count',
                null,
                InputArgument::OPTIONAL,
                'Limit how much records get updated by single process',
                self::DEFAULT_ITERATION_COUNT
            )
            ->setHelp(
                <<<'EOT'
The command <info>%command.name%</info> updates field
data_int in configured Legacy Storage database for a given field type.

Fields will be checked and updated only if not already in UTC timezone.

<warning>During the script execution the database should not be modified.

You are advised to create a backup or execute a dry run before 
proceeding with actual update.</warning>

<warning>This command should be only ran ONCE.</warning>

Since this script can potentially run for a very long time, to avoid memory
exhaustion run it in production environment using <info>--env=prod</info> switch.
EOT
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        /* @var \Doctrine\DBAL\Connection $databaseHandler */
        $this->connection = $this->getContainer()->get('ezpublish.api.search_engine.legacy.connection');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $iterationCount = (int) $input->getOption('iteration-count');
        $this->dryRun = $input->getOption('dry-run');
        $this->mode = $input->getOption('mode');

        $from = $input->getOption('from');
        $to = $input->getOption('to');

        if ($from && !$this->validateDateTimeString($from, $output)) {
            return;
        }
        if ($to && !$this->validateDateTimeString($to, $output)) {
            return;
        }

        if ($from) {
            $this->from = $this->dateStringToTimestamp($from);
        }

        if ($to) {
            $this->to = $this->dateStringToTimestamp($to);
        }

        $consoleScript = $_SERVER['argv'][0];

        if (getenv('INNER_CALL')) {
            $this->timezone = $input->getArgument('timezone');
            $this->processTimestamps((int) $input->getOption('offset'), $iterationCount);
            $output->writeln($this->done);
        } else {
            $timezone = $input->getArgument('timezone');
            $this->timezone = $this->validateTimezone($timezone, $output);

            $modeTxt = 'Converting timestamps for both ezdate and ezdatetime fields.';

            switch ($this->mode) {
                case 'date':
                    $modeTxt = 'Converting timestamps for ezdate fields.';
                    break;
                case 'datetime':
                    $modeTxt = 'Converting timestamps for ezdatetime fields.';
                    break;
            }

            $output->writeln(
                [
                    $modeTxt,
                    'Calculating number of Content Object Attributes to update...',
                ]
            );
            $count = $this->countEzDateObjectAttributes();
            $output->writeln(
                [
                    sprintf('Found total of Content Objects Atributes for update: %d', $count),
                    '',
                ]
            );

            if ($count == 0) {
                $output->writeln('Nothing to process, exiting.');

                return;
            }

            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                '<question>Are you sure you want to proceed?</question> ',
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('');

                return;
            }

            $progressBar = $this->getProgressBar($count, $output);
            $progressBar->start();

            for ($offset = 0; $offset < $count; $offset += $iterationCount) {
                $processBuilder = new ProcessBuilder(
                    [$this->getPhpPath(), $consoleScript, $this->getName(), $this->timezone]
                );

                if ($from) {
                    $processBuilder->add('--from=' . $from);
                }
                if ($to) {
                    $processBuilder->add('--to=' . $to);
                }
                $processBuilder->add('--mode=' . $this->mode);
                $processBuilder->add('--offset=' . $offset);
                $processBuilder->add('--iteration-count=' . $iterationCount);
                $processBuilder->add('--env=' . $input->getOption('env'));
                if ($this->dryRun) {
                    $processBuilder->add('--dry-run');
                }
                $processBuilder->setEnv('INNER_CALL', 1);
                $process = $processBuilder->getProcess();
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new RuntimeException($process->getErrorOutput());
                }

                $this->done += (int) $process->getOutput();

                $progressBar->advance($this->done);
            }

            $progressBar->finish();
            $output->writeln(
                [
                    '',
                    sprintf('Done: %d', $this->done),
                ]
            );
        }
    }

    /**
     * @param int $offset
     * @param int $limit
     */
    protected function processTimestamps($offset, $limit)
    {
        $ezDateObjectAttributes = $this->getEzDateObjectAttributes($offset, $limit);

        $dateTimeInUTC = new DateTime();
        $dateTimeInUTC->setTimezone(new DateTimeZone('UTC'));

        foreach ($ezDateObjectAttributes as $ezDateObjectAttribute) {
            $timestamp = $ezDateObjectAttribute['data_int'];
            $dateTimeInUTC->setTimestamp($timestamp);
            $newTimestamp = $this->convertToUtcTimestamp($timestamp);

            if (!$this->dryRun) {
                $this->updateTimestampToUTC($ezDateObjectAttribute['id'], $newTimestamp);
            }
            ++$this->done;
        }
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    protected function getEzDateObjectAttributes($offset, $limit)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('a.id, a.data_int')
            ->from('ezcontentobject_attribute', 'a')
            ->join('a', 'ezcontentobject_version', 'v', 'a.contentobject_id = v.contentobject_id')
            ->where(
                $query->expr()->in(
                    'a.data_type_string',
                    $query->createNamedParameter($this->getFields(), Connection::PARAM_STR_ARRAY)
                )
            )
            ->andWhere('a.data_int is not null')
            ->andWhere('a.data_int > 0')
            ->andWhere('v.version = a.version')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($this->from) {
            $query
                ->andWhere('v.modified >= :fromTimestamp')
                ->setParameter('fromTimestamp', $this->from);
        }
        if ($this->to) {
            $query
                ->andWhere('v.modified <= :toTimestamp')
                ->setParameter('toTimestamp', $this->to);
        }

        $statement = $query->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return int
     */
    protected function countEzDateObjectAttributes()
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('count(*) as count')
            ->from('ezcontentobject_attribute', 'a')
            ->join('a', 'ezcontentobject_version', 'v', 'a.contentobject_id = v.contentobject_id')
            ->where(
                $query->expr()->in(
                    'a.data_type_string',
                    $query->createNamedParameter($this->getFields(), Connection::PARAM_STR_ARRAY)
                )
            )
            ->andWhere('a.data_int is not null')
            ->andWhere('a.data_int > 0')
            ->andWhere('v.version = a.version');

        if ($this->from) {
            $query
                ->andWhere('v.modified >= :fromTimestamp')
                ->setParameter('fromTimestamp', $this->from);
        }
        if ($this->to) {
            $query
                ->andWhere('v.modified <= :toTimestamp')
                ->setParameter('toTimestamp', $this->to);
        }

        $statement = $query->execute();

        return (int) $statement->fetchColumn();
    }

    /**
     * @param int $timestamp
     * @return int
     */
    protected function convertToUtcTimestamp($timestamp)
    {
        $dateTimeZone = new DateTimeZone($this->timezone);
        $dateTimeZoneUTC = new DateTimeZone('UTC');

        $dateTime = new DateTime(null, $dateTimeZone);
        $dateTime->setTimestamp($timestamp);
        $dateTimeUTC = new DateTime($dateTime->format('Y-m-d H:i:s'), $dateTimeZoneUTC);

        return $dateTimeUTC->getTimestamp();
    }

    /**
     * @param string $dateTimeString
     * @param OutputInterface $output
     * @return bool
     */
    protected function validateDateTimeString($dateTimeString, OutputInterface $output)
    {
        try {
            new \DateTime($dateTimeString);
        } catch (\Exception $exception) {
            $output->writeln(
                [
                    'The --from and --to options must be a valid Date string.',
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * @param string $timezone
     * @param OutputInterface $output
     * @return string
     */
    protected function validateTimezone($timezone, OutputInterface $output)
    {
        if (!$timezone) {
            $timezone = date_default_timezone_get();
            $output->writeln(
                [
                    sprintf('No Timezone set, using server Timezone: %s', $timezone),
                    '',
                ]
            );
        } else {
            if (!\in_array($timezone, timezone_identifiers_list())) {
                $output->writeln(
                    [
                        sprintf('% is not correct Timezone.', $timezone),
                        '',
                    ]
                );

                return;
            }

            $output->writeln(
                [
                    sprintf('Using timezone: %s', $timezone),
                    '',
                ]
            );
        }

        return $timezone;
    }

    /**
     * Return configured progress bar helper.
     *
     * @param int $maxSteps
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    protected function getProgressBar($maxSteps, OutputInterface $output)
    {
        $progressBar = new ProgressBar($output, $maxSteps);
        $progressBar->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%'
        );

        return $progressBar;
    }

    /**
     * @param int $contentAttributeId
     * @param int $newTimestamp
     */
    protected function updateTimestampToUTC(
        $contentAttributeId,
        $newTimestamp
    ) {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('ezcontentobject_attribute', 'a')
            ->set('a.data_int', $newTimestamp)
            ->set('a.sort_key_int', $newTimestamp)
            ->where('a.id = :id')
            ->setParameter(':id', $contentAttributeId);

        $query->execute();
    }

    /**
     * @return string
     */
    private function getPhpPath()
    {
        if ($this->phpPath) {
            return $this->phpPath;
        }
        $phpFinder = new PhpExecutableFinder();
        $this->phpPath = $phpFinder->find();
        if (!$this->phpPath) {
            throw new RuntimeException(
                'The php executable could not be found, it\'s needed for executing parable sub processes, so add it to your PATH environment variable and try again'
            );
        }

        return $this->phpPath;
    }

    /**
     * @return string
     */
    private function getFields()
    {
        $fields = [];

        if ($this->mode == 'date' || $this->mode == 'all') {
            $fields[] = 'ezdate';
        }
        if ($this->mode == 'datetime' || $this->mode == 'all') {
            $fields[] = 'ezdatetime';
        }

        return $fields;
    }

    /**
     * @param $dateString string
     * @throws \Exception
     * @return int
     */
    private function dateStringToTimestamp($dateString)
    {
        $date = new \DateTime($dateString);

        return $date->getTimestamp();
    }
}
