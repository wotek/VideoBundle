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
    $loader = new Loader\PhpFileLoader(
      $container, new FileLocator(__DIR__.'/../Resources/config')
    );
    /**
     * Configured providers
     *
     * @var array
     */
    $container->setParameter('wtk_video_providers', $config['providers']);
    // Idea: Move bundle configuration from app/config/config.yml
    // to [bundle]/Resources/config/providers/[provider].yml
    // and ability to merge those. Main app configuration should be
    // able to override local config
    $loader->load('services.php');
  }

}
