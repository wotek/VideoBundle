<?php
namespace Wtk\VideoBundle\Entity\Movie;

use Doctrine\ORM\EntityRepository;
use Wtk\VideoBundle\Entity\Movie;
use Wtk\VideoBundle\Entity\Movie\RepositoryInterface;

class Repository extends EntityRepository implements RepositoryInterface
{
  /**
   *
   * @param  string $checksum
   * @return array
   */
  public function getByChecksum($checksum)
  {
    return $this->findByChecksum($checksum);
  }

  /**
   * @param  string $provider
   * @return array
   */
  public function getProviderMovies($provider)
  {
    return $this->findByProvider($provider);
  }

  /**
   * @param  string $remote_id
   * @param  string $provider
   * @return array
   */
  public function getByRemoteId($remote_id, $provider)
  {
    return $this->findBy(array(
      'remote_id' => $remote_id,
      'provider'  => $provider,
    ));
  }

  /**
   * @param  Movie  $movie
   * @return void
   */
  public function persist(Movie $movie)
  {
    $em = $this->getEntityManager();
    $em->persist($movie);
    $em->flush();
  }
}
