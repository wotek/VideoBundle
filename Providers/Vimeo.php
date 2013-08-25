<?php
namespace Wtk\VideoBundle\Providers;

use Wtk\VideoBundle\Providers\Provider\AbstractProvider;
use Wtk\VideoBundle\Providers\Provider\Client\Vimeo as VimeoClient;
use Wtk\VideoBundle\VideoFile;
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
   * @param  VideoFile $file
   */
  public function upload(VideoFile $file)
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

    if(false == $client->checkTicket($ticket['id']))
    {
      throw new ProviderException(
        sprintf("Ticket %s has expired.", $ticket['id'])
      );
    }

    $this->log(sprintf("Got ticket: %s", $ticket['id']));

    /**
     * 3. Transfer video data
     */
    $this->log("Starting file upload...");

    $is_success = $client->upload($ticket['endpoint'], $file);

    $verified = $client->verify($ticket['endpoint'], $file);
    $this->log(sprintf("Upload verified?: %s", $verified ? 'Yes' : 'No'));

    if($verified)
    {
      $video_id = $client->complete($ticket['id'], $file->getFilename());
      $this->log(sprintf("Uploaded video id : %d", $video_id));
    }
    {
      // Handle upload resume
    }

  }

  /**
   * @return Wtk\VideoBundle\Providers\Provider\Client\Vimeo
   */
  protected function getClient()
  {
    return VimeoClient::factory($this->getConfig());
  }

}
