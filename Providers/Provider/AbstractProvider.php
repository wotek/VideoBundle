<?php
namespace Wtk\VideoBundle\Providers\Provider;
/**
 * @author wzalewski
 */
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

  /**
   * @return Guzzle\Service\Client
   */
  abstract protected function getClient();

  /**
   * @return array
   */
  protected function getConfig()
  {
    return $this->config;
  }

}
