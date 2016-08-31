<?php
/**
 * File containing the DeleteUsersFromTrashCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveOrphanRecordsFromEzUserTablesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ezplatform:upgrade:remove-orphan-user-records')
            ->setDescription('Removes all records from ezuser and ezuser_setting
             tables having no corresponding record in ezcontentobject table. 
             See https://jira.ez.no/browse/EZP-25644');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler; */
        $connection = $this->getContainer()->get('ezpublish.api.storage_engine.legacy.dbhandler');
        
        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select(
            $connection->quoteColumn('id')
        )->from(
            $connection->quoteTable('ezcontentobject')
        );

        $statement = $selectQuery->prepare();
        $statement->execute();
        $contentObjectIdsResults = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $contentObjectIds = array_values($contentObjectIdsResults);

        $tablesAndColumns = [
            'ezuser' => 'contentobject_id',
            'ezuser_setting' => 'user_id',
        ];

        foreach ($tablesAndColumns as $table => $column) {
            $ezUserDeleteOrphansQuery = $connection->createDeleteQuery();
            $ezUserDeleteOrphansQuery->deleteFrom(
                $connection->quoteTable($table)
            )->where(
                $ezUserDeleteOrphansQuery->expr->not(
                    $ezUserDeleteOrphansQuery->expr->in(
                        $connection->quoteColumn($column),
                        $contentObjectIds
                    )
                )
            );

            $ezUserDeleteOrphansQuery->prepare()->execute();
            $output->writeln('Deleting orphan records from ' . $table . '.');
        }
    }
}
