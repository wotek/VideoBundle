<?php
namespace Wtk\VideoBundle\Providers\Provider;

use Symfony\Component\HttpFoundation\File\File;

interface ProviderInterface {
  function upload(File $file);
  function get($id);
}
