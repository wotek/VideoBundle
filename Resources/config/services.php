<?php

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;

/**
 * Rili, that helps? Guessing how to get simple stuff?
 * Wasted 1h on that. Configuration over convention - f* yeah!
 * There is probably simple way. Cannot waste another hour diggin through
 * documentation.
 */

/**
 * Movies DAO
 */
$container->setDefinition('wtk.movies.repository', new Definition(
    // class
    'Wtk\VideoBundle\Entity\Movie\Repository',
    // arguments
    array('WtkVideoBundle:Movie')
))
->setFactoryService(
    new Reference('doctrine.orm.default_entity_manager')
)->setFactoryMethod(
    'getRepository'
);

/**
 * Providers factory
 */
$container->setDefinition(
  'wtk.movies.provider.factory',
  new Definition(
    'Wtk\VideoBundle\Providers\Factory',
    array()
  )
);

/**
 * Service
 */
$container->setDefinition(
  'wtk.movies',
  new Definition(
    'Wtk\VideoBundle\Service\Movies',
    array(
      new Reference('wtk.movies.repository')
    )
  )
);





