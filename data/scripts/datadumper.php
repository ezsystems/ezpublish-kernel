#!/usr/bin/env php
<?php
/**
 * A Simple eZ Publish database data dumper using ezcDbFactory
 *
 * Note: This is intended to be replaced by Doctrine DBAL stuff for abstract
 * SQL database needs like schema and possibly the absolute minimum data
 * needed in the database to function.
 *
 * + For future data dump we aim to write a tool that writes to and from a
 * XML format suitable for XMLReader & XMLWriter so it can be used for large
 * data-sets, for speed it might need to use SPI. But that would make it
 * unusable for import from other sources, for that API with its validation
 * and conventions is a must.
 */

require "./vendor/autoload.php";

if (false === isset($argv[1]) || false === isset($argv[2])) {
    echo 'Usage: ', PHP_EOL,
         basename(__FILE__), ' "mysql://user:password@localhost/database_name" <dump-file>', PHP_EOL;
    exit(1);
}

$db = ezcDbFactory::create($argv[1]);

// Get eZ tables
$tables = array();
$result = $db->query('SHOW TABLES');
while ($row = $result->fetch(PDO::FETCH_COLUMN)) {
    // Only add ez tables but not ezx_ (eznetwork) tables
    if (strpos($row, 'ez') === 0 && strpos($row, 'ezx_') !== 0) {
        $tables[] = $row;
    }
}

// Get data
$fixture = array();
foreach ($tables as $table) {
    $result = $db->query('SELECT * FROM ' . $table);

    $fixture[$table] = array();
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $fixture[$table][] = $row;
    }
}

// Var export fixture, reduce indentation & write to dump-file
$fixture = "<?php\n\nreturn " . str_replace(array( "\n  ", "\n  ", "\n   ", " => \n" ), array( "\n", "\n ", "\n  ", " =>\n" ), var_export($fixture, true)) . ";\n\n";
if (file_put_contents($argv[2], $fixture) === false) {
    echo "file_put_contents returned false, might be wrong dump file name or missing permissions";
} else {
    echo count($table) . " tables successfully written to file {$argv[2]}";
}
