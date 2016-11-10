# IO migration

> Added in 6.7 / 2016.12

### Context
This document describes a command script that can migrate binary files
from an IO repository to another.

A common use-case is to migrate local files, stored with the default IO
configuration, to a new IO configuration.

### 
We will consider the following IO configuration for this document:

```yaml
ez_io:
    binarydata_handlers:
        // native binarydata handler, filesystem
        default:
        nfs:
            flysystem:
                adapter: nfs_adapter
        aws_s3:
    metadata_handlers:
        // native metadata handler, filesystem
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
Migrating is done by running the `ezplatform:io:migrate-files` console script:
from one set of io handlers to another:
```
php app/console ezplatform:io:migrate-files
--from=<from_metadata_handler>,<from_binarydata_handler>
--to=<to_metadata_handler>,<to_binarydata_handler>
```

Migration expects that IO handlers specified in the `from` and `to` 
arguments are configured in the `ez_io` section.

Without any argument, the command migrates files from the `default`
io handlers, that use the filesystem for storage. With the configuration
above, executing the script without any argument would be like running:

`php app/console ezplatform:io:migrate-files --from=default,default --to=nfs,dfs`

In most cases, once io has been configured, existing files can be migrated
by running the command without any argument.

By using different arguments, any kind of migration is possible. This would
migrate files from DFS to AWS/S3.
`php app/console ezplatform:io:migrate-files --from=nfs,dfs--to=aws_s3,aws_s3`

### Migration handlers

There are 3 types of files to migrate, with corresponding legacy tables:
- binary files (ezbinaryfile)
- media files (ezmedia)
- images (ezimagefile)

These are implemented using MigrationHandlerInterface and can be looked up in
a MigrationHandlerRegistry.

### Replicate Adapter

Migration between flysystem-based adapters (currently local files and NFS) can
be done with [Replicate Adapter](http://flysystem.thephpleague.com/adapter/replicate/)

### Open questions
If a handler is identical in from and to, is it ignored?
What are the script options?
Should we allow running the script without arguments?
How is output/logging done?
