<?php

namespace Wtk\VideoBundle\Entity\Movie;

use Wtk\VideoBundle\Entity\Movie;

interface RepositoryInterface {
  function getByChecksum($checksum);
  function getProviderMovies($provider);
  function getByRemoteId($remote_id, $provider);
  function persist(Movie $movie);
}
