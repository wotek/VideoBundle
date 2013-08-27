<?php

namespace Wtk\VideoBundle\Providers\Provider\Client;

use Guzzle\Common\Collection;
use Guzzle\Plugin\Oauth\OauthPlugin;
use Guzzle\Service\Client;
use Guzzle\Service\Description\ServiceDescription;

use Wtk\VideoBundle\VideoFile;
use Guzzle\Http\Message\Response;

use Guzzle\Http\EntityBody;
use Guzzle\Http\IoEmittingEntityBody;

class Vimeo extends Client {
  /**
   * Creates new Vimeo client
   *
   * @param  array  $config
   *
   * @return Wtk\VideoBundle\Providers\Provider\Client\Vimeo
   */
  public static function factory($config = array())
  {
    // Create a new Vimeo client
    $client = new self('http://vimeo.com/api/rest/v2', $config);

    // Ensure that the OauthPlugin is attached to the client
    $client->addSubscriber(new OauthPlugin($config));

    // Set service description
    $client->setDescription(
      ServiceDescription::factory(
        __DIR__ . '/../../../Resources/config/guzzle/service_vimeo.json'
      )
    );

    return $client;
  }

  /**
   * Stream file to remote destination
   *
   * @param  VideoFile   $file
   * @return bool
   */
  public function upload($endpoint, VideoFile $file, \Closure $callback = null)
  {
    $body = new IoEmittingEntityBody(
      EntityBody::factory(
        fopen($file->getRealPath(), 'r'),
        $file->getSize()
      )
    );

    if($callback)
    {
      $body->getEventDispatcher()->addListener('body.read', $callback);
    }

    $request = $this->put($endpoint, null, $body);
    $request->setHeader('Content-Type', $file->getMimeType());

    $response = $request->send();

    if($response->isSuccessful())
    {
      return true;
    }

    return $this->handleError($response);
  }

  /**
   * Completes upload. Tells vimeo to enqueue file
   * transcoding.
   *
   * This call will return the video_id, which you can then use in
   * other calls (to set the title, description, privacy, etc.).
   * If you do not call this method, the video will not be processed.
   *
   * @api_method: vimeo.videos.upload.complete
   *
   * @return int video_id
   */
  public function complete($ticket_id, $filename)
  {
    $command = $this->getCommand('complete',
      array(
        'ticket_id' => $ticket_id,
        'filename' => $filename,
      )
    );
    $command->execute();

    $response = $command->getResponse();

    if($response->isSuccessful())
    {
      $response = $response->json();
      return $response['ticket']['video_id'];
    }

    return $this->handleError($response);
  }

  /**
   * Get movie data
   *
   * @api_method: vimeo.videos.upload.getInfo
   *
   * @param  string $id
   * @return JSON
   */
  public function getInfo($id)
  {
    $command = $this->getCommand('getInfo', array('video_id' => $id,))
      ->execute()
    ;

    $response = $command->getResponse();

    if($response->isSuccessful())
    {
      return $response->json();
    }

    return $this->handleError($response);
  }

  /**
   * Returns user quota
   *
   * @api_method: vimeo.videos.upload.getQuota
   *
   * @return array
   */
  public function getQuota()
  {
    $command = $this->getCommand('getQuota');
    $command->execute();

    $response = $command->getResponse();

    if($response->isSuccessful())
    {
      $response = $response->json();
      /**
       * Returns:
       * array (
       *   'free' => '524288000',
       *   'max' => '524288000',
       *   'resets' => '6',
       *   'used' => '0',
       *   ),
       */
      return $response['user']['upload_space'];
    }

    return $this->handleError($response);
  }

  /**
   * @api_method: vimeo.videos.upload.getTicket
   * @return JSON
   *
   * Example:
   *
   * {
   *   "id":"abcdef124567890",
   *   "endpoint":"http:\/\/1.2.3.4:8080\/upload?ticket_id=abcdef124567890",
   *   "max_file_size":"524288000"
   * }
   */
  public function getTicket()
  {
    $command = $this->getCommand('getTicket');
    $command->execute();

    $response = $command->getResponse();

    if($response->isSuccessful())
    {
      $response = $response->json();
      return $response['ticket'];
    }

    return $this->handleError($response);
  }

  /**
   * Checks if ticket is still valid
   *
   * @api_method: vimeo.videos.upload.checkTicket
   *
   * @return boolean
   */
  public function checkTicket($ticket_id)
  {
    $command = $this->getCommand('checkTicket',
      array('ticket_id' => $ticket_id)
    );
    $command->execute();

    $response = $command->getResponse();

    if($response->isSuccessful())
    {
      $response = $response->json();
      return 1 === (int) $response['ticket']['valid'];
    }

    return $this->handleError($response);
  }

  /**
   * Verify uploaded file
   *
   * @return
   */
  public function verify($endpoint, VideoFile $file)
  {
    /**
     * To check how much of a file has transferred, perform the exact same
     * PUT request without the file data and with the header
     * Content-Range: bytes * / *
     *
     * @see  https://developer.vimeo.com/apis/advanced/upload#streaming-step4
     */
    $request = $this->put($endpoint);
    $request->setHeader('Content-Range', 'bytes */*');
    $request->setHeader('Content-Length', '0');
    $response = $request->send();
    /**
     * Expected response:
     *
     * HTTP/1.1 308
     * Content-Length: 0
     * Range: bytes=0-1000
     */
    $response = $request->send();

    if(308 === $response->getStatusCode())
    {
      return true;
    }

    return false;
  }

  public function setTitle($video_id, $title)
  {
    $command = $this->getCommand('setTitle',
      array(
        'video_id' => $video_id,
        'title' => $title
      )
    );
    $command->execute();

    $response = $command->getResponse();

    if($response->isSuccessful())
    {
      return true;
    }

    return $this->handleError($response);
  }

  public function setVideoDescription($video_id, $description)
  {
    $command = $this->getCommand('setDescription',
      array(
        'video_id' => $video_id,
        'description' => $description
      )
    );
    $command->execute();

    $response = $command->getResponse();

    if($response->isSuccessful())
    {
      return true;
    }

    return $this->handleError($response);
  }

  /**
   * Handle response
   *
   * @param  Response $response
   * @param  Closure  $onSuccess
   * @param  Closure  $onFailure
   * @return void
   */
  protected function handleResponse(
    Response $response,
    Closure $onSuccess = null,
    Closure $onFailure = null)
  {

  }

  /**
   * Default error handler
   *
   * @param  Response $response
   * @return void
   */
  protected function handleError(Response $response)
  {

  }

}
