<?php

namespace Wtk\VideoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
  /**
   * {@inheritDoc}
   */
  public function getConfigTreeBuilder()
  {
    $treeBuilder = new TreeBuilder();
    // To be changed to something more generic:
    $rootNode = $treeBuilder->root('wtk_video');
    /**
     * Let's define what's allowed and required
     * in configuration section.
     *
     * @todo : Each provider may have different options names.
     *         So, there is need for separate Configuration
     *         classes for provider
     */
    $rootNode
      ->children()
        ->arrayNode('providers')
          ->requiresAtLeastOneElement()
          ->example('Set name of provider: vimeo, youtube')
          ->info('Configures video provider API access')
          ->prototype('array')
            ->children()
              ->scalarNode('consumer_secret')
                ->isRequired(true)
              ->end()
              ->scalarNode('consumer_key')
                ->isRequired(true)
              ->end()
              ->scalarNode('token')
                ->isRequired(true)
              ->end()
              ->scalarNode('token_secret')
                ->isRequired(true)
              ->end()
            ->end()
          ->end()
        ->end()
      ->end()
    ;

    return $treeBuilder;
  }
}
