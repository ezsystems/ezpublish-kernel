# eZ Platform AWS/S3 IO configuration Tutorial

This tutorial will guide you through the steps required to store and serve
eZ Platform binaries from Amazon S3.

## AWS configuration

The first thing you need to do is create a bucket, and get valid key and
secret to this account.

If you have never used S3 before, here is a simple step by step guide.
You can find more details on the S3 documentation.

### Quick walkthrough

1. Go to the Amazon S3 management console
2. Click on Create bucket, and create a bucket with a unique name, and
   the region you want to use.
3. Click on your username, and on Security Credentials.
4. Click on users
5. Click on create new users
6. Enter a username of your choice
6. Click on "show credentials", and either copy the key+secret, or
   download the credentials. Close.
7. Click on "Policies" in the left menu, and on "Get started"
8. Click on "Create policy", then on "Create your own policy"
10. Paste the following policy configuration, replacing <your-bucket-name>
    with the name of the bucket you have created:
   ```
   {
       "Version": "2012-10-17",  
       "Statement": [ 
           {
                "Sid": "Stmt1440611618416",
               "Action": ["s3:ListAllMyBuckets" ],
               "Effect": "Allow",
               "Resource": "arn:aws:s3:::*" 
           },
           { 
               "Sid": "Stmt1440610197576", 
               "Action": "s3:*",
               "Effect": "Allow",
               "Resource": [
                   "arn:aws:s3:::your-bucket-name",
                   "arn:aws:s3:::your-bucket-name/*" 
               ] 
           } 
       ] 
   }
   ```
   It will grant EzUser permissions to list your buckets, and do anything
   on the configured bucket.
11. Click on "Users" in the left menu, and on the name of the user you
   created earlier.
12. Go to the "Permissions" tab, and click on "Attach policy"
13. Select the policy you have created above (use the filter), and click
    on "Attach policy"

## eZ Platform configuration

We are going to configure eZ Platform to _store and serve_ content 
images and binary files to your S3 bucket.

Doing so is going to require a flysystem plugin to communicate with S3,
as well as a service and a few configuration blocks.

### Install the AWS-S3 Flysystem plugin
First, you need to install the aws-s3 flysystem adapter. Go to the root
of your ezplatform installation, and run:

```
composer require league/flysystem-aws-s3-v3
```

### Configure an S3 client service
The Flysystem S3 adapter uses the official client from amazon. Create a
service in `app/config/config.yml`.:

```
services:
    ezplatform.s3_client:
        class: Aws\S3\S3Client
        arguments:
            -
                version: 'latest'
                region: '<region>'
                credentials:
                    key: "<key>"
                    secret: '<secret>'
```

Replacing <key> and <secret> with the ones you copied/downloaded above,
and <region> with the amazon region that matches the one you chose when
you created the bucket.

### Flysystem S3 adapter
eZ Platform uses Flysyste to handle content binary files. Let's create
one that uses our S3 service:

```
oneup_flysystem:
    adapters:
        ezplatform.s3_flysystem_adapter:
            awss3v3:
                client: ezplatform.s3_client
                bucket: <bucket>
                prefix: ~
```

Replace <bucket> with your bucket's name.

### Platform IO data handlers
eZ Platform distinguishes binary and meta data handling. You need to
create a binarydata_handler and a metadata_handler, that both use the
s3 adapter created above:

```
ez_io:
    metadata_handlers:
        s3:
            flysystem:
                adapter: ezplatform.s3_flysystem_adapter
    binarydata_handlers:
        s3:
            flysystem:
                adapter: ezplatform.s3_flysystem_adapter

```

### Configure platform to use those handlers
In `ezplatform.yml`, add an `io` block in the default scope, or in the
site's group:

```
ezpublish:
    system:
        default:
             io:
                metadata_handler: s3
                binarydata_handler: s3
                url_prefix: 'https://s3-<region>.amazonaws.com/<bucket>/'
```

Replace <region> and <bucket> with your own values.

## Migrating existing content
To migrate existing images, copy the contents of the 
`<ezplatform>/web/<vardir>/site/storage` directory to your S3 bucket.

## The end
From now on, any image or binary file you create will be stored in your
S3 bucket. The backoffice and your site will use http://s3... urls for
images.

Image conversion will still work transparently, using local copies of
the files
