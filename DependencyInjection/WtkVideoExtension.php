<?php

namespace Wtk\VideoBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class WtkVideoExtension extends Extension
{
  /**
   * @var PhpFileLoader
   */
  protected $loader;

  /**
   * {@inheritDoc}
   */
  public function load(array $configs, ContainerBuilder $container)
  {
    $configuration = new Configuration();
    $config = $this->processConfiguration($configuration, $configs);

    $loader = $this->getFileLoader($container);

    /**
     * Configured providers
     *
     * @var array
     */
    $providers = array_keys($config['providers']);

    foreach($providers as $provider)
    {
      /**
       * Idea: Move bundle configuration from app/config/config.yml
       * to [bundle]/Resources/config/providers/[provider].yml
       * and ability to merge those. Main app configuration should be
       * able to override local config.
       */
      $this->configureProvider(
        $provider, $config['providers'][$provider], $container
      );
      /**
       * Load provider into DI container with given configuration
       */
      $loader->load($this->resolveProviderConfig($provider));
    }

    $loader->load('services.php');
  }

  /**
   * @param  string           $provider
   * @param  ContainerBuilder $container
   * @return void
   */
  protected function configureProvider($provider, array $config, ContainerBuilder $container)
  {
    /**
     * Set provider config
     */
    $container->setParameter($provider . '_config', $config);
  }

  /**
   * @param  string $provider
   * @return string
   */
  protected function resolveProviderConfig($provider)
  {
    return 'providers/' . strtolower($provider) . '.php';
  }

  /**
   * @param  ContainerBuilder $container
   * @return PhpFileLoader
   */
  protected function getFileLoader(ContainerBuilder $container)
  {
    if(null === $this->loader)
    {
      $this->loader = new Loader\PhpFileLoader(
        $container, new FileLocator(__DIR__.'/../Resources/config')
      );
    }

    return $this->loader;
  }

}
