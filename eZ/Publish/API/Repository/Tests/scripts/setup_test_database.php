#!/usr/bin/env php
<?php

require_once __DIR__ . '/../../../../../../bootstrap.php';

if ( isset( $argv[1] ) )
{
    $_ENV['DATABASE'] = $argv[1];
}

$setupFactory = new eZ\Publish\API\Repository\Tests\SetupFactory\Legacy();

$setupFactory->getRepository( true );
