# IO migration

> Added in 6.10

**NB: This feature is experimental, for the time being. Use with caution!**

### Context
This document describes a command script that can migrate binary files
from an IO repository to another.

A common use-case is to migrate local files, stored with the default IO
configuration, to a new IO configuration.

We will consider the following IO configuration for this document:

```yaml
ez_io:
    binarydata_handlers:
        # native binarydata handler, filesystem
        default:
        nfs:
            flysystem:
                adapter: nfs_adapter
        aws_s3:
    metadata_handlers:
        # native metadata handler, filesystem
        default:
        dfs:
            legacy_dfs_cluster:
                connection: doctrine.dbal.dfs_connection
        aws_s3:

ezpublish:
    system:
        default:
            io:
                metadata_handler: dfs
                binarydata_handler: nfs
```

### Console script
Migrating is done by running the `ezplatform:io:migrate-files` console script
from one set of io handlers to another:
```
php app/console ezplatform:io:migrate-files
--from=<from_metadata_handler>,<from_binarydata_handler>
--to=<to_metadata_handler>,<to_binarydata_handler>
```

Migration expects that IO handlers specified in the `from` and `to` 
arguments are configured in the `ez_io` section.

Without any argument, the command migrates files from the `default`
io handlers, which use the filesystem for storage, to the first defined
non-default io handlers. With the configuration above, executing the script
without any argument would be like running:

`php app/console ezplatform:io:migrate-files --from=default,default --to=nfs,dfs`

In most cases, once io has been configured, existing files can be migrated
by running the command without any argument.

By using different arguments, any kind of migration is possible. This would
migrate files from DFS to AWS/S3.
`php app/console ezplatform:io:migrate-files --from=nfs,dfs --to=aws_s3,aws_s3`

Other script options are:
```
--list-io-handlers         List available IO handlers
--bulk-count=BULK-COUNT    Number of files processed at once [default: 100]
--dry-run                  Execute a dry run
```

Script logging follows common settings, i.e. by default the migration will be
logged if you use `--env=dev`.
