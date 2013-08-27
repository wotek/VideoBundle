<?php
namespace Wtk\VideoBundle\Providers;

interface FactoryInterface
{
  function get($provider, array $config = array());
}
