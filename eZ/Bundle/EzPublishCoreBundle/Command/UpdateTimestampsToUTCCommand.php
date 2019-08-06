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
use Symfony\Component\Process\Process;
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
    const MAX_TIMESTAMP_VALUE = 2147483647;
    const DEFAULT_ITERATION_COUNT = 100;
    const MODES = [
        'date' => ['ezdate'],
        'datetime' => ['ezdatetime'],
        'all' => ['ezdate', 'ezdatetime'],
    ];

    /** @var int */
    protected $done = 0;

    /** @var string */
    protected $timezone;

    /** @var string */
    private $mode;

    /** @var string */
    private $from;

    /** @var string */
    private $to;

    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /** @var string */
    private $phpPath;

    /** @var bool */
    private $dryRun;

    protected function configure()
    {
        $this
            ->setName('ezplatform:timestamps:to-utc')
            ->setDescription('Updates ezdate & ezdatetime timestamps to UTC')
            ->addArgument(
                'timezone',
                InputArgument::OPTIONAL,
                'Original timestamp\'s TimeZone',
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

This is to be used either when migrating from eZ Publish to eZ Platform 
(when using platform backend instead of legacy), or when upgrading legacy 
to v2019.03 which has been adapted to use UTC.

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

        if (!array_key_exists($this->mode, self::MODES)) {
            $output->writeln(
                sprintf('Selected mode is not supported, please use one of: %s', implode(', ', array_keys(self::MODES)))
            );

            return;
        }

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
            $this->processTimestamps((int) $input->getOption('offset'), $iterationCount, $output);
            $output->writeln($this->done);
        } else {
            $timezone = $input->getArgument('timezone');
            $this->timezone = $this->validateTimezone($timezone, $output);

            $output->writeln([
                sprintf('Converting timestamps for fields: %s', implode(', ', self::MODES[$this->mode])),
                'Calculating number of Field values to update...',
            ]);
            $count = $this->countTimestampBasedFields();
            $output->writeln([
                sprintf('Found total of Field values for update: %d', $count),
                '',
            ]);

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
                $processScriptFragments = [
                    $this->getPhpPath(),
                    $consoleScript,
                    $this->getName(),
                    $this->timezone,
                    '--mode=' . $this->mode,
                    '--offset=' . $offset,
                    '--iteration-count=' . $iterationCount,
                ];

                if ($from) {
                    $processScriptFragments[] = '--from=' . $from;
                }
                if ($to) {
                    $processScriptFragments[] = '--to=' . $to;
                }
                if ($this->dryRun) {
                    $processScriptFragments[] = '--dry-run';
                }

                $process = new Process(
                    implode(' ', $processScriptFragments)
                );

                $process->setEnv(['INNER_CALL' => 1]);
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new RuntimeException($process->getErrorOutput());
                }

                $doneInProcess = (int) $process->getOutput();
                $this->done += $doneInProcess;

                $progressBar->advance($doneInProcess);
            }

            $progressBar->finish();
            $output->writeln([
                '',
                sprintf('Done: %d', $this->done),
            ]);
        }
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function processTimestamps($offset, $limit, $output)
    {
        $timestampBasedFields = $this->getTimestampBasedFields($offset, $limit);

        $dateTimeInUTC = new DateTime();
        $dateTimeInUTC->setTimezone(new DateTimeZone('UTC'));

        foreach ($timestampBasedFields as $timestampBasedField) {
            $timestamp = $timestampBasedField['data_int'];
            $dateTimeInUTC->setTimestamp($timestamp);
            $newTimestamp = $this->convertToUtcTimestamp($timestamp);

            //failsafe for int field limitation (dates/datetimes after 01/19/2038 @ 4:14am (UTC))
            if ($newTimestamp <= self::MAX_TIMESTAMP_VALUE && !$this->dryRun) {
                $this->updateTimestampToUTC($timestampBasedField['id'], $timestampBasedField['version'], $newTimestamp);
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
    protected function getTimestampBasedFields($offset, $limit)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('a.id, a.version, a.data_int')
            ->from('ezcontentobject_attribute', 'a')
            ->join('a', 'ezcontentobject_version', 'v', 'a.contentobject_id = v.contentobject_id')
            ->where(
                $query->expr()->in(
                    'a.data_type_string',
                    $query->createNamedParameter(self::MODES[$this->mode], Connection::PARAM_STR_ARRAY)
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
     * Counts affected timestamp based fields using captured "mode", "from" and "to" command options.
     *
     * @return int
     */
    protected function countTimestampBasedFields()
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('count(*) as count')
            ->from('ezcontentobject_attribute', 'a')
            ->join('a', 'ezcontentobject_version', 'v', 'a.contentobject_id = v.contentobject_id')
            ->where(
                $query->expr()->in(
                    'a.data_type_string',
                    $query->createNamedParameter(self::MODES[$this->mode], Connection::PARAM_STR_ARRAY)
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
            $output->writeln('The --from and --to options must be a valid Date string.');

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
            $output->writeln([
                sprintf('No Timezone set, using server Timezone: %s', $timezone),
                '',
            ]);
        } else {
            if (!\in_array($timezone, timezone_identifiers_list())) {
                $output->writeln([
                    sprintf('% is not correct Timezone.', $timezone),
                    '',
                ]);

                return;
            }

            $output->writeln([
                sprintf('Using timezone: %s', $timezone),
                '',
            ]);
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
     * @param int $contentAttributeVersion
     * @param int $newTimestamp
     */
    protected function updateTimestampToUTC(
        $contentAttributeId,
        $contentAttributeVersion,
        $newTimestamp
    ) {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('ezcontentobject_attribute', 'a')
            ->set('a.data_int', $newTimestamp)
            ->set('a.sort_key_int', $newTimestamp)
            ->where('a.id = :id')
            ->andWhere('a.version = :version')
            ->setParameter(':id', $contentAttributeId)
            ->setParameter(':version', $contentAttributeVersion);

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
