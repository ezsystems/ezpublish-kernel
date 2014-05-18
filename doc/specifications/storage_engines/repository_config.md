# Repository configuration

## Description

As of eZ Publish 5.3, configuration for content repositories are application wide.
This makes them easily reusable across SiteAccesses by simply mentioning their alias.

## Example

### Using default storage engine and default connection
```yaml
ezpublish:
    repositories:
        # Defining repository with alias "main"
        # Default storage engine is used, with default connection
        # Equals to:
        # main: { engine: legacy, connection: <defaultConnectionName> }
        main: ~

    system:
        # All members of my_siteaccess_group will use "main" repository
        # No need to set "repository", it will take the first defined repository by default
        my_siteaccess_group:
            # ...
```

### All explicit
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
