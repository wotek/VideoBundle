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
   * Stream file to remote destination.
   * Don't worry about chunking file. Guzzle actually takes
   * care of that.
   *
   * @param  VideoFile   $file
   * @return bool
   */
  public function upload($endpoint, VideoFile $file)
  {
    $body = EntityBody::factory(
      fopen($file->getRealPath(), 'r'), $file->getSize()
    );
    /**
     * @todo : Rewrite using service descriptions.
     */
    $request = $this->put($endpoint, null, $body);
    $request->setHeader('Content-Type', $file->getMimeType());

    $response = $request->send();

    return $this->handleResponse(
      $response,
      function($response)
      {
        return true;
      }
    );
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
    return $this->executeCommand('complete',
      array(
        'ticket_id' => $ticket_id,
        'filename'  => $filename,
      ),
      function($response)
      {
        $response = $response->json();
        return (int) $response['ticket']['video_id'];
      }
    );
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
    return $this->executeCommand('getInfo',
      array('video_id' => $id),
      function($response)
      {
        return $response->json();
      }
    );
  }

  /**
   * Returns user quota
   *
   * @api_method: vimeo.videos.upload.getQuota
   * array (
   *   'free' => '524288000',
   *   'max' => '524288000',
   *   'resets' => '6',
   *   'used' => '0',
   *   ),
   *
   * @return array
   */
  public function getQuota()
  {
    return $this->executeCommand('getQuota',
      array(),
      function($response)
      {
        $response = $response->json();
        return $response['user']['upload_space'];
      }
    );
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
    return $this->executeCommand('getTicket', array(),
      function($response)
      {
        $response = $response->json();
        return $response['ticket'];
      }
    );
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
    return $this->executeCommand('checkTicket',
      array('ticket_id' => $ticket_id),
      function($response)
      {
        $response = $response->json();
        return 1 === (int) $response['ticket']['valid'];
      }
    );
  }

  /**
   * Verify uploaded file
   *
   * @return
   */
  public function verify($endpoint, VideoFile $file)
  {
    /**
     * @todo : Rewrite using service descriptions.
     */
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

  /**
   * Sets video title
   *
   * @param int     $video_id
   * @param string  $title
   */
  public function setTitle($video_id, $title)
  {
    return $this->executeCommand('setTitle',
      array(
        'video_id'  => $video_id,
        'title'     => $title
      ),
      function($response)
      {
        return true;
      }
    );
  }

  /**
   * Sets video description
   *
   * @param int     $video_id
   * @param string  $description
   */
  public function setVideoDescription($video_id, $description)
  {
    return $this->executeCommand('setDescription',
      array(
        'video_id'    => $video_id,
        'description' => $description
      ),
      function($response)
      {
        return true;
      }
    );
  }

  /**
   * Executes API command.
   *
   * @param  string   $command    Command to be executed
   * @param  array    $params     Command params
   * @param  Closure  $onSuccess  On success callback
   * @param  Closure  $onFailure  On failure callback
   * @return void
   */
  protected function executeCommand($command, array $params = array(), \Closure $onSuccess = null, \Closure $onFailure = null)
  {
    /**
     * Get command from service container
     */
    $command = $this->getCommand($command, $params);
    /**
     * Make request
     */
    $command->execute();
    /**
     * Handle response
     */
    return $this->handleResponse(
      $command->getResponse(), $onSuccess, $onFailure
    );
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
    \Closure $onSuccess = null,
    \Closure $onFailure = null)
  {
    if($response->isSuccessful())
    {
      if(null !== $onSuccess)
      {
        return $onSuccess($response);
      }

      return $response;
    }

    if(null !== $onFailure)
    {
      return $onFailure($response);
    }

    return $this->handleError($response);
  }

  /**
   * Default error handler
   *
   * @param  Response $response
   */
  protected function handleError(Response $response)
  {
    /**
     * Misc::ignore($response) - might be usefull
     */
    throw new ErrorResponseException("Invalid API response.");
  }

}
