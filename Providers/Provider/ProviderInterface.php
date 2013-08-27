<?php
namespace Wtk\VideoBundle\Providers\Provider;

use Wtk\VideoBundle\VideoFile;

interface ProviderInterface {
  function upload(VideoFile $file);
  function get($id);
  function setTitle($id, $title);
  function setDescription($id, $description);
  function getId();
}
