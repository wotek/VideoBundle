<?php

namespace Wtk\VideoBundle\Entity\Movie;

use Wtk\VideoBundle\Entity\Movie;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Collections\Selectable;

interface RepositoryInterface extends ObjectRepository, Selectable {
  function getByChecksum($checksum);
  function getProviderMovies($provider);
  function getByRemoteId($remote_id, $provider);
  function persist(Movie $movie);
}
