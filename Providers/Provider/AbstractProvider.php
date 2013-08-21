<?php

namespace Wtk\VideoBundle\Providers\Provider;

use Guzzle\Http\Client;
use Guzzle\Plugin\Oauth\OauthPlugin;
use Guzzle\Plugin\Cache\CachePlugin;

abstract class AbstractProvider implements ProviderInterface
{
  /**
   * @var array
   */
  protected $config = array();

  /**
   * @param array $config
   */
  public function __construct(array $config = array())
  {
    $this->config = $config;
  }

  abstract public function getClient();

  /**
   * @return array
   */
  protected function getConfig()
  {
    return $this->config;
  }


}
