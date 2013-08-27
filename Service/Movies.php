<?php
namespace Wtk\VideoBundle\Service;

use Wtk\VideoBundle\VideoFile;
use Wtk\VideoBundle\Entity\Movie\RepositoryInterface;
use Wtk\VideoBundle\Providers\Factory as ProviderFactory;
use Wtk\VideoBundle\Entity\Movie;
/**
 * @author wzalewski
 */
class Movies {
  /**
   * @var ProviderFactory
   */
  protected $providers;

  /**
   *
   * @var RepositoryInterface
   */
  protected $repository;

  /**
   * @param RepositoryInterface $repository
   * @param ProviderFactory     $providers
   */
  public function __construct(RepositoryInterface $repository, ProviderFactory $providers) {
    $this->repository = $repository;
    $this->providers = $providers;
  }

  /**
   * Uploads file using given provider
   *
   * @param  string        $provider
   * @param  VideoFile     $file
   *
   * @return int           Video id
   */
  public function upload($provider, VideoFile $file)
  {
    /**
     * Make it possible to inject provider by-passing factory.
     *
     * @todo  : This is a entry point for getting rid of duplicated code
     *          in MoviesCommand.
     */
    if(! $provider instanceof ProviderInterface)
    {
      $provider = $this->getProvider($provider);
    }

    /**
     * Check if given file exists in DB
     */


    return $provider->upload($file);
  }

  /**
   * Use provider factory method to get provider instance
   *
   * @param string $provider Provider name
   * @param array  $config   Provider config
   *
   * @return ProviderInterface
   */
  public function getProvider($provider, array $config = array())
  {
    return $this->providers->get($provider, $config);
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
