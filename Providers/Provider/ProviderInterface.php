<?php

namespace Wtk\VideoBundle\Providers\Provider;

interface ProviderInterface {
  function upload($file);
  function get($id);
}
