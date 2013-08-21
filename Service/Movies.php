<?php
namespace Wtk\VideoBundle\Service;

use Symfony\Component\HttpFoundation\File\File;
use Wtk\VideoBundle\Entity\Movie\RepositoryInterface;
use Wtk\VideoBundle\Entity\Movie;
use Wtk\VideoBundle\Providers\Provider\ProviderInterface;

class Movies {
  /**
   * @var ProviderInterface
   */
  protected $provider;

  /**
   *
   * @var RepositoryInterface
   */
  protected $repository;

  /**
   * @param RepositoryInterface       $repository
   */
  public function __construct(RepositoryInterface $repository) {
    $this->repository = $repository;
  }

  /**
   * Uploads file
   *
   * @param  File     $file
   * @param  string   $provider
   * @return int|null
   */
  public function upload(File $file)
  {
    return $this->getProvider()->upload($file);
  }

  /**
   * @param ProviderInterface $provider
   */
  public function setProvider(ProviderInterface $provider)
  {
    $this->provider = $provider;
  }

  /**
   * @return ProviderInterface
   */
  public function getProvider()
  {
    if(null === $this->provider)
    {
      throw new \Exception("Missing provider instance");
    }

    return $this->provider;
  }

  /**
   * Return movie object
   *
   * @param  int $movie_id
   * @return Movie|null
   */
  public function getById($movie_id)
  {
    return $this->getRepository()->find($movie_id);
  }

  /**
   * Returns movie by file's checksum
   *
   * @param  string $checksum
   * @return Movie|null
   */
  public function getByChecksum($checksum)
  {
    return $this->getRepository()->getByChecksum($checksum);
  }

  /**
   * Insert/Update object
   *
   * @param  Movie  $movie
   * @return void
   */
  public function save(Movie $movie)
  {
    return $this->getRepository()->persist($movie);
  }

  /**
   * Return movies DAO
   *
   * @return RepositoryInterface
   */
  protected function getRepository()
  {
    return $this->repository;
  }
}
