# DoctrineBundle integration

## Description
As of eZ Publish 5.3, database settings for legacy storage engine is supplied by DoctrineBundle.
For further information, see [DoctrineBundle configuration](https://github.com/doctrine/DoctrineBundle/blob/master/Resources/doc/configuration.rst#doctrine-dbal-configuration).

> **Important note**: Doctrine ORM is **not** provided by default.
> If you want to use it, you will need to add `doctrine/orm` as a dependency.

```yaml
doctrine:
    dbal:
        default_connection: my_connection_name
        connections:
            my_connection_name:
                driver:   pdo_mysql
                host:     localhost
                port:     3306
                dbname:   my_database
                user:     my_user
                password: my_password
                charset:  UTF8

            another_connection_name:
                # ...

ezpublish:
    repositories:
        first_repository: { engine: legacy, connection: my_connection_name, config: {} }
        second_repository: { engine: legacy, connection: another_connection_name, config: {} }

    # ...

    system:
        my_first_siteaccess:
            repository: first_repository

            # ...

        my_second_siteaccess:
            repository: second_repository
```
