<?php

namespace Wtk\VideoBundle\Providers\Provider;

use Guzzle\Http\Client;
use Guzzle\Plugin\Oauth\OauthPlugin;
use Guzzle\Plugin\Cache\CachePlugin;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressHelper;

abstract class AbstractProvider implements ProviderInterface
{
  /**
   * @var array
   */
  protected $config = array();

  /**
   * @var OutputInterface
   */
  protected $logger;

  /**
   * @var ProgressHelper
   */
  protected $progress;

  /**
   * @param array $config
   */
  public function __construct(array $config = array())
  {
    $this->config = $config;
  }

  public function setLogger(OutputInterface $output)
  {
    $this->logger = $output;
  }

  protected function log($message)
  {
    if(null === $this->logger)
    {
      return;
    }

    $this->logger->writeln(
      sprintf("<info>%s</info>", $message)
    );
  }

  public function setProgressHelper(ProgressHelper $helper)
  {
    $this->progress = $helper;
    $this->progress->start($this->logger, 10);
  }

  protected function updateProgress($complete = false)
  {
    if(null === $this->progress)
    {
      return;
    }

    $this->progress->advance();

    if($complete)
    {
      $this->progress->finish();
    }
  }

  abstract protected function getClient();

  /**
   * @return array
   */
  protected function getConfig()
  {
    return $this->config;
  }

}
