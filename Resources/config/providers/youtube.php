<?php

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;

$container->setDefinition('wtk.movies.provider.youtube', new Definition(
    'Wtk\Bundle\VimeoBundle\Providers\Youtube',
    array(
      'youtube',
      '%youtube_config%'
    )
))
->setFactoryService(
    new Reference('wtk.movies.provider.factory')
)->setFactoryMethod(
    'factory'
);
