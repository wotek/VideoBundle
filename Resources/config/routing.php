<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('wtk_video_homepage', new Route('/hello/{name}', array(
    '_controller' => 'WtkVideoBundle:Default:index',
)));

return $collection;
