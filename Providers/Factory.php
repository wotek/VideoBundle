<?php
namespace Wtk\VideoBundle\Providers;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * @author wzalewski
 */
class Factory {
  /**
   * Available providers
   *
   * @var array
   */
  protected $providers = array();

  /**
   * Providers configuration
   *
   * @var array
   */
  protected $configuration = array();

  /**
   * @var OutputInterface
   */
  protected static $logger;

  /**
   * @param array $configuration Configured providers
   */
  public function __construct(array $configuration)
  {
    $this->configuration = $configuration;
    /**
     * Set available providers
     *
     * @var array
     */
    $this->providers = array_keys($configuration);
  }

  /**
   * Factory method. Returns pre-configured provider.
   *
   * @param  string $provider   Provider name
   * @param  array  $config     Need to override provider configuration?
   *
   * @return ProviderInterface
   */
  public function get($provider, array $config = array())
  {
    if(!in_array($provider, $this->providers))
    {
      throw new \Wtk\VideoBundle\Providers\Provider\Exception(
        sprintf("Invalid provider given. Supported providers: %s",
          implode(', ', $this->providers)
        )
      );
    }
    /**
     * Figure out provider classname
     */
    $classname = $this->resolveClassname($provider);

    if(!class_exists($classname))
    {
      throw new \Wtk\VideoBundle\Providers\Provider\Exception(
        "Class $classname does not exists"
      );
    }

    /**
     * Default configuration
     * @var array
     */
    $default = $this->getConfig($provider);

    if(0 < count($config))
    {
      /**
       * @todo : This should be taken by Configuration class
       *
       * Required key names
       *
       * @var array
       */
      $required = array_keys($default);

      if(0 < count($unsupported = array_diff(array_keys($config), $required)))
      {
        throw new \Wtk\VideoBundle\Providers\Provider\Exception(
          sprintf("Unrecognized config key: %s", implode(', ', $unsupported))
        );
      }
    }

    $config = array_merge($default, $config);

    $instance = new $classname($config);

    /**
     * For verbose purposes only:
     */
    if(self::$logger)
    {
      $instance->setLogger(self::$logger);
    }

    return $instance;
  }

  /**
   * @param  OutputInterface $logger
   * @return void
   */
  public static function registerLogger(OutputInterface $logger)
  {
    self::$logger = $logger;
  }

  /**
   * @param  string $provider
   * @return string
   */
  protected function resolveClassname($provider)
  {
    /**
     * See if there is some helper like in Zend framework
     * aka Symfony_Class_Loader?
     */
    return __NAMESPACE__ . '\\' . ucfirst($provider);
  }

  /**
   * Returns provider configuration
   *
   * @param  string $provider
   * @return array
   */
  protected function getConfig($provider)
  {
    return $this->configuration[$provider];
  }

}
