============================================
Introduction of Doctrine DBAL in Persistence
============================================

Both Legacy and Sql-Ng persistence abstractions are using Zeta Components
Database component. This specification evaluates the possibility to replace
Zeta Components Database with Doctrine DBAL.

Reasons for the evaluation are:

- Doctrine has better maintenance than Zeta Components
- Integration into Symfony ecosystem is simpler (through DIC) and allows to re-use connections in request
- Support for Oracle

Analysis Current Abstraction
----------------------------

Zeta Components Database is used to abstract the following SQL concerns.
These are hidden in implementations of database gateways and
for Search in little helpers that generate the query parts.

- Query Building
    - Select, Update, Insert and Delete queries use a QueryBuilder object
- Quoting of identifiers (columns, tables)
- Returns non-casted rows of rows for all SELECT queries
    - Column names and cases are part of the API of the Gateways
    - Not currently abstracted for Oracle naming (possibility to do it though)
- Identifier Generation
- Very low level PDO based type abstraction

Concerns that other seperated parts handle:

- Converting and casting the SQL rows into objects (Mapper)

DBAL abstractions
-----------------

The DBAL can cover the following current abstractions

- Identifier generation
- Query Building: Select, Update, Delete
- Quoting of identifiers, based on keywords in underlying platform
- Knowledge of SQL Dialects default for result case
- Type abstraction for update queries

Missing abstractions are:

- INSERT statement support, including integration with identifier generation
- CRUD Abstraction (Without the select part) for tables that allows
  converting an object with properties into converted columns and
  insert/update/delete it as well as handling identity generation.
- API for column aliasing and case-handling
- Database schema is currently defined in plain SQL and should be converted
  to Doctrine Schema API for cross-database support. Given the size it
  might be simpler to write a simple Code-Generator Visitor for a loaded
  Schema from the database.

Refactoring Approach
--------------------

Zeta Components Query API and Doctrine Query API for SELECTs are very similar,
allowing the opportunity to switch them in a very simple way through a small
compatibility abstraction for the Handler and Query APIs and simple search and
replace operations for the SQL Expression generation.

The Update operations are a bit more cumbersome to change, because the
parameter binding and type binding works so differently.

Refactoring steps:

1. Introduce a DBAL connection object with compatibility layer for the conversion
2. Determine search/replaceable strings when converting gateways
3. Inside the handler, create a Doctrine DBAL Connection using the PDO from EzcDbHandler (?), optionally reuse existing?

Repeat for every gateway:

4. Copy one Gateway at a time from EzcDatabase to DoctrineDatabase DBAL implementation
5. Switch Persistence Handler to return the new Doctrine implementation
6. Extend ExceptionConvertion Gateway to handle `DBALException` as well.

Tasks
-----

1. Introduce basic DBAL abstractions
2. Legacy Persistence
    2.1 Refactor all Gateways and Search Condition Generators
    2.2 Convert SQL schema into `Doctrine\DBAL\Schema\Schema` instance to allow
        generating SQL for all database platforms.
3. Sql-NG Persistence
    3.1 Refactor all Gateways and Search Condition Generators
    3.2 Convert SQL schema into `Doctrine\DBAL\Schema\Schema` instance to allow
        generating SQL for all database platforms.

API
---

Currently the aliasing/quoting code is pretty dominant in the Gateways, because
of the way the ezc Query Objects work. Hiding this implementation detail
behind a simple Table Gateway helps simplify the code alot. ::

    <?php
    interface TableGateway
    {
        public function __construct(Connection $conn, TableMetadata $metadata);
        public function insert(array $data);
        public function update(array $data, array $where);
        public function delete(array $where);
        public function createSelectQuery();
        public function createUpdateQuery();
        public function createDeleteQuery();
        public function createInsertQuery();
    }

    class TableMetadata
    {
        public $name;
        public $sequenceName;
        public $primaryKeys = array();
        public $columns = array();
    }

