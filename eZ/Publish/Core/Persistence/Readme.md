# Core Persistence

Contains various implementations of SPI\Persistence, and common libraries for use by them.

Folder   | Description
---------|------------
Cache    | SPI Persistence implementation for cache using Stash (decorated)
Database | Interfaces for emulating Zeta Components Database as it was used in the beginning (planned to be removed)
Doctrine | Doctrine DBAL implementation of "Database" (planned to be removed in favour of direct Doctrine use)
Legacy   | SPI Persistence implementation for sql database as used in eZ Publish 4.x "legacy"
Solr     | Implementation of search handler (planned to be moved out of Persistence as own 'Search')
Test     | Test for common functionality in this folder, including TransformationProcessor (todo: move to ./Common)
