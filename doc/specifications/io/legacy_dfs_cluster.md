# Legacy DFS cluster

> Added in 5.4 / 2014.11

## Summary
Adds a `legacy_dfs_cluster` IO metadata handler. It stores metadata in the `ezdfsfile` table from the legacy
database. It is meant to be used to write binarydata on a locally mounted NFS server.

## Configuration
This handler isn't enabled by default. If you enable it, you also need to [enable and
configure it on your legacy instance](https://doc.ez.no/eZ-Publish/Technical-manual/5.x/Features/Clustering/Setting-it-up-for-an-eZDFSFileHandler).

Once this is done, assuming that your database is named `ezdfs`, configure it, for instance in `ezpublish.yml`:

```yaml
# set the handlers
ezpublish:
    system:
        default:
            io:
                metadata_handler: dfs
                binarydata_handler: nfs

# declare the handlers
ez_io:
    binarydata_handlers:
        nfs:
            local:
                adapter: nfs_adapter
    metadata_handlers:
        dfs:
            legacy_dfs_cluster:
                # Service ID of Doctrine DBAL connection for ezdfs
                connection: doctrine.dbal.ezdfs_connection

# new doctrine connection
doctrine:
    dbal:
        connections:
            ezdfs:
                driver: pdo_mysql
                host: 127.0.0.1
                port: 3306
                dbname: ezdfs
                user: root
                password: "rootpassword"
                charset: UTF8

# new flysystem adapter
oneup_flysystem:
    adapters:
        nfs_adapter:
            local:
                # The last part, $var_dir$/$storage_dir$, is required for legacy compatibility
                directory: "/path/to/nfs/$var_dir$/$storage_dir$"
```

**Important**: take good note of the $var_dir$/$storage_dir$ part for the NFS path. Legacy expects this path to exist
on the NFS mount in order to be able to read and write files.

## Web server rewrite rules.
The default eZ Publish rewrite rules will let image requests be served directly from disk. With native support,
files matching `^/var/([^/]+/)?storage/images(-versioned)?/.*` have to be sent to the normal app.php

In any case, this specific rewrite rule must be placed without the ones that "ignore" image files and just let the
web server serve the files.

### Apache
```
RewriteRule ^/var/([^/]+/)?storage/images(-versioned)?/.* /app.php [L]
```

### nginx
```
rewrite "^/var/([^/]+/)?storage/images(-versioned)?/(.*)" "/app.php" break;
```
