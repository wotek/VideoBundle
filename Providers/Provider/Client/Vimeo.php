<?php

namespace Wtk\VideoBundle\Providers\Provider\Client;

use Guzzle\Http\Client;
use Guzzle\Plugin\Oauth\OauthPlugin;
use Guzzle\Plugin\Cache\CachePlugin;

class Vimeo {
  /**
   * https://github.com/vimeo/vimeo-php-lib/blob/master/vimeo.php
   */
  const API_ENDPOINT = 'http://vimeo.com/api/rest/v2';

  /**
   * @var Guzzle\Http\Client
   */
  protected $client;

  /**
   * @param array $config
   */
  public function __construct(array $config)
  {
    /**
     * Create guzzle client
     * @var Guzzle\Http\Client
     */
    $this->client = new Client(self::API_ENDPOINT);
    /**
     * Make it support OAuth authorization
     */
    $this->client->addSubscriber(
      new OauthPlugin($config)
    );
  }

  /**
   * Performs API authentications
   *
   * @return
   */
  public function authenticate()
  {
    /**
     * This part is handled by OAuth plugin.
     */
  }

  /**
   * Uploads file
   *
   * @param  File   $file
   * @return
   */
  public function upload(File $file, $use_chunks = true)
  {}

  /**
   * Returns user quota
   *
   * @return
   */
  public function getQuota()
  {}

  /**
   * Verify uploaded file
   *
   * @return
   */
  public function verify()
  {}

  public function command($method, $command, $args)
  {}
}
