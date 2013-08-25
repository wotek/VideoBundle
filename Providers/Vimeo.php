<?php
namespace Wtk\VideoBundle\Providers;

use Wtk\VideoBundle\Providers\Provider\AbstractProvider;
use Wtk\VideoBundle\Providers\Provider\Client\Vimeo as VimeoClient;
use Symfony\Component\HttpFoundation\File\File;
use Wtk\VideoBundle\Providers\Provider\Exception as ProviderException;

class Vimeo extends AbstractProvider {
  /**
   * Retrieve data about movie with give id
   *
   * @param  string $id
   * @return array
   */
  public function get($id)
  {
    return $this->getClient()->getInfo($id);
  }

  /**
   * Uploads video to Vimeo
   *
   * @param  File $file
   */
  public function upload(File $file)
  {
    $this->log(
      sprintf("Uploading file %s ...", $file->getFilename())
    );
    /**
     * 4. Verfiy upload
     * 5. Complete process -> return video_id
     */
    $client = $this->getClient();
    /**
     * 1. Check user quota
     */
    $this->log("Receiving quota information from API..");

    $quota = $client->getQuota();

    if($file->getSize() > $freespace = $quota['free'])
    {
      throw new ProviderException(
        "Cannot upload given file. Maximum allowed file size is: $freespace"
      );
    }
    /**
     * 2. Get an upload ticket
     */
    $this->log("Fetching upload ticket");

    $ticket = $client->getTicket();

    $this->log(
      sprintf("Got ticket: %s", json_encode($ticket))
    );
    /**
     * 3. Transfer video data
     */
    $this->log("Starting file upload...");
    $client->upload(
      $ticket['endpoint'],
      $file
    );

    // return $this->getClient()->upload($file);
  }

  /**
   * @return Wtk\VideoBundle\Providers\Provider\Client\Vimeo
   */
  protected function getClient()
  {
    return VimeoClient::factory($this->getConfig());
  }

}
