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
   * @param int     $id
   * @param string  $title
   */
  public function setTitle($id, $title)
  {
    $this->getClient()->setTitle($id, $title);
  }

  /**
   * @param int     $id
   * @param string  $description
   */
  public function setDescription($id, $description)
  {
    $this->getClient()->setDescription($id, $description);
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

    $progress_callback = null;
    if($this->progress)
    {
      $helper = $this->progress;
      $progress_callback = function($event) use ($helper)
      {
        // We'll get > 100%. EntityBody payload. Dont worry ;)
        $helper->advance($event['length']);
      };
    }

    $is_success = $client->upload($ticket['endpoint'], $file, $progress_callback);
    /**
     * Succeess upload video id: 73084036
     */
    /**
     * 4. Verfiy upload
     */
    $verified = $client->verify($ticket['endpoint'], $file);
    $this->log(sprintf("Upload verified?: %s", $verified ? 'Yes' : 'No'));

    if($is_success & $verified)
    {
      /**
       * 5. Complete process
       */
      $video_id = $client->complete($ticket['id'], $file->getFilename());
      $this->log(sprintf("Uploaded video id : %d", $video_id));
    }
    else
    {
      throw new ProviderException(
        "Could not verify uploaded file or upload failed. \nResuming upload currently not implemented. Sorry."
      );
    }

    return $video_id;
  }

  /**
   * @return Wtk\VideoBundle\Providers\Provider\Client\Vimeo
   */
  protected function getClient()
  {
    return VimeoClient::factory($this->getConfig());
  }

}
