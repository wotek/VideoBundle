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
     */
    if(!$provider instanceof ProviderInterface)
    {
      $provider = $this->getProvider($provider);
    }

    if($this->getByChecksum($file->getChecksum()))
    {
      throw new Exception(
        "Duplicated file. File with the same checksum already exists"
      );
    }

    $movie = new Movie;

    $movie->setProvider($provider->getId());
    $movie->setChecksum($file->getChecksum());

    $this->save($movie);

    $remote_id = $provider->upload($file);

    $movie->setRemoteId($remote_id);
    $movie->setCompleted();

    $this->save($movie);

    return $remote_id;
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
   * Get movie(s)
   *
   * @param  int $movie_id
   * @return Movie|null
   */
  public function get($movie_id = null)
  {
    if(null !== $movie_id)
    {
      return $this->getRepository()->find($movie_id);
    }

    return $this->getRepository()->findAll();
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
