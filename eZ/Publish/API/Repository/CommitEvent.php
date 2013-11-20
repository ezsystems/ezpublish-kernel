<?php

namespace eZ\Publish\API\Repository;

interface CommitEvent
{
    /**
     * Action to be executed after succesfull commit of the current transaction.
     */
    public function onTransactionCommitted();
}
