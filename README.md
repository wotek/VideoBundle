VideoBundle
----------------------------------

VideoBundle is a result of me learning Symfony2 Framework.
I do hope you find it useful.

### Features

* Uploads video through provider API
* Keeps track of uploaded files

### Supported API's

* Vimeo
* Youtube [incoming]

### Todo

* Youtube API
* 100% test coverage

1) Requirements
----------------------------------

* Guzzle (@see https://github.com/guzzle/guzzle)

### Use Composer (*recommended*)

If you don't have Composer yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:

    curl -s http://getcomposer.org/installer | php

### Install required dependencies

    # Add dependencies
    # Composer will automaticaly download & install & modify your composer.json
    composer require guzzle/guzzle:~3.7

### Install VideoBundle

    composer install wotek/video-bundle

2) Configuration
----------------------------------

### Register bundle

    # source: app/AppKernel.php
    $bundles = array(
      // ...
      new Wtk\VideoBundle\WtkVideoBundle(),
      // ...
    );

### Update database schema

Movies bundle uses one table ``movies`` where movie metadata is held.

Create database if not already have it?

    app/console doctrine:database:create

Create database tables:

    app/console doctrine:schema:create

... or migrate database:

    app/console doctrine:schema:update

### Providers

By for now bundle supports only Vimeo API.
You need to configure access to API in `app/config/config.yml` configuration file.

Example:

    wtk_video:
      providers:
          vimeo:
              consumer_secret:    API Consumer
              consumer_key:       API Consumer Key
              token:              Token
              token_secret:       Token secret

3) Usage
----------------------------------

Bundle provides (only) command line interface to manage video uploads.

#### Available commands:

##### Movie upload

    movies:upload [--provider="..."] [--path="..."] [--title[="..."]] [--description[="..."]]

    Options:
     --provider            Provider name
     --path                Path to file
     --title               Uploaded video title
     --description         Video description

Currently there is not verbose information when file is being uploaded.

Example usage:

    $ app/console movies:upload --path=tofik.mov \
    --title="Tofik" \
    --description="Tofik is just jumping around" \
    --provider=vimeo

    Uploading tofik.mov this might take a while. Hold on. Go get a coffee
    File id: 73208538 uploaded.


##### List uploaded movies

    movies:list

    Outputs:

    +----+----------+----------------------------------+----------+-----------+
    | ID | RemoteID | Checksum                         | Provider | Completed |
    +----+----------+----------------------------------+----------+-----------+
    | 1  | 73206060 | 92513815a44ea80099f46bf8a871cd62 | vimeo    | 1         |
    | 2  | 73208538 | 445df168c31e07ef806c788f8420e1fa | vimeo    | 1         |
    +----+----------+----------------------------------+----------+-----------+











