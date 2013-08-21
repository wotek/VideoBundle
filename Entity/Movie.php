<?php

namespace Wtk\VideoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Movie
 *
  * @ORM\Table(
 *   name="movies",
 *   indexes={
 *     @ORM\Index(name="remote_idx", columns={"remote_id"}),
 *     @ORM\Index(name="checksum_idx", columns={"checksum"}),
 *     @ORM\Index(name="provider_idx", columns={"provider"}),
 *     @ORM\Index(name="provider_checksum_idx", columns={"provider", "checksum"})
 *   }
 * )
 * @ORM\Entity(repositoryClass="Wtk\VideoBundle\Entity\Movie\Repository")
 */
class Movie {
  /**
   * @var integer
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var integer
   *
   * @ORM\Column(name="remote_id", type="integer")
   */
  private $remoteId;

  /**
   * md5_file checksum from uploaded file
   *
   * @var string
   *
   * @ORM\Column(name="checksum", type="string", length=32)
   */
  private $checksum;

  /**
   * Movie provider (Vimeo/YT/etc.)
   *
   * @var string
   *
   * @ORM\Column(name="provider", type="string", length=32)
   */
  private $provider;


  /**
   * Get id
   *
   * @return integer
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set remoteId
   *
   * @param integer $remoteId
   * @return Movie
   */
  public function setRemoteId($remoteId)
  {
    $this->remoteId = $remoteId;

    return $this;
  }

  /**
   * Get remoteId
   *
   * @return integer
   */
  public function getRemoteId()
  {
    return $this->remoteId;
  }

  /**
   * Set checksum
   *
   * @param string $checksum
   * @return Movie
   */
  public function setChecksum($checksum)
  {
    $this->checksum = $checksum;

    return $this;
  }

  /**
   * Get checksum
   *
   * @return string
   */
  public function getChecksum()
  {
    return $this->checksum;
  }

  /**
   * Set provider
   *
   * @param string $provider
   * @return Movie
   */
  public function setProvider($provider)
  {
    $this->provider = $provider;

    return $this;
  }

  /**
   * Get provider
   *
   * @return string
   */
  public function getProvider()
  {
    return $this->provider;
  }
}
