<?php

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;

$container->setDefinition('wtk.movies.provider.vimeo', new Definition(
    'Wtk\VideoBundle\Providers\Vimeo',
    array(
      'vimeo',
      '%vimeo_config%'
    )
))
->setFactoryService(
    new Reference('wtk.movies.provider.factory')
)->setFactoryMethod(
    'factory'
);
