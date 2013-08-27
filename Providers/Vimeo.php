<?php
namespace Wtk\VideoBundle\Providers;

use Wtk\VideoBundle\Providers\Provider\AbstractProvider;
use Wtk\VideoBundle\Providers\Provider\Client\Vimeo as VimeoClient;
use Wtk\VideoBundle\VideoFile;
use Wtk\VideoBundle\Providers\Provider\Exception as ProviderException;
/**
 * @author wzalewski
 */
class Vimeo extends AbstractProvider {
  /**
   * Retrieve data about movie with give id
   *
   * @param  string $id
   *
   * @return array
   */
  public function get($id)
  {
    return $this->getClient()->getInfo($id);
  }

  /**
   * @param int     $id
   * @param string  $title
   *
   * @return bool
   */
  public function setTitle($id, $title)
  {
    return $this->getClient()->setTitle($id, $title);
  }

  /**
   * @param int     $id
   * @param string  $description
   *
   * @return bool
   */
  public function setDescription($id, $description)
  {
    return $this->getClient()->setVideoDescription($id, $description);
  }

  /**
   * Uploads video to Vimeo
   *
   * @param  VideoFile $file
   * @throws ProviderException
   *
   * @return integer Uploaded video id
   */
  public function upload(VideoFile $file)
  {
    $client = $this->getClient();
    /**
     * 1. Check user quota
     */
    if(false === $this->hasEnoughFreeSpace($file->getSize()))
    {
      throw new ProviderException(
        "Cannot upload given file. Not enough space."
      );
    }

    /**
     * 2. Get an upload ticket
     */
    list($ticket_id, $endpoint) = $this->getTicket();

    /**
     * 3. Transfer video data
     */
    $is_success = $client->upload($endpoint, $file);

    if(false === $is_success)
    {
      throw new ProviderException("File upload failed");
    }

    /**
     * 4. Verfiy upload
     */
    $verified = $client->verify($endpoint, $file);

    if(false === $verified)
    {
      throw new ProviderException("Cannot verify uploaded file.");
    }

    /**
     * 5. Complete process
     */
    $video_id = $client->complete($ticket_id, $file->getFilename());

    return $video_id;
  }

  /**
   * Get upload ticket
   *
   * @return array
   */
  protected function getTicket()
  {
    $client = $this->getClient();

    $ticket = $client->getTicket();

    if(false == $client->checkTicket($ticket['id']))
    {
      throw new ProviderException(
        sprintf("Ticket %s has expired.", $ticket['id'])
      );
    }

    return array($ticket['id'], $ticket['endpoint'],);
  }

  /**
   * Check if we can even upload file about this size
   *
   * @param  int  $required    Required space for file
   * @return boolean
   */
  protected function hasEnoughFreeSpace($required)
  {
    $quota = $this->getClient()->getQuota();

    return $required < $quota['free'];
  }

  /**
   * @return Wtk\VideoBundle\Providers\Provider\Client\Vimeo
   *
   * @todo Should be protected, is public for verbose purposes only.
   */
  public function getClient()
  {
    return VimeoClient::factory($this->getConfig());
  }

}
