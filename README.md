1) Requirements
----------------------------------

Before you can use Vimeo bundle you need to satisfy bundle dependencies:

* Guzzle (@see https://github.com/guzzle/guzzle)

### Use Composer (*recommended*)

If you don't have Composer yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:

    curl -s http://getcomposer.org/installer | php

### Install required dependencies

    # Add dependencies
    # Composer will automaticaly download & install & modify your composer.json
    composer require guzzle/guzzle:~3.7

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

By now bundle supports YouTube and Vimeo API's.
You need to configure access to API in `app/config/config.yml` configuration file.

Example:

    wtk_video:
      providers:
          vimeo:
              consumer_secret:    API Consumer
              consumer_key:       API Consumer Key
              token:              Token
              token_secret:       Token secret



