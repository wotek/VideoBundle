<?php

namespace Wtk\VideoBundle\Providers\Provider;

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

  /**
   * @return Guzzle\Service\Client
   */
  abstract protected function getClient();

  /**
   * @param OutputInterface $output
   */
  public function setLogger(OutputInterface $output)
  {
    $this->logger = $output;
  }

  /**
   * @param  string $message
   * @return void
   */
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

  /**
   * @param ProgressHelper $helper
   */
  public function setProgressHelper(ProgressHelper $helper)
  {
    $this->progress = $helper;
  }

  /**
   * @return array
   */
  protected function getConfig()
  {
    return $this->config;
  }

}
