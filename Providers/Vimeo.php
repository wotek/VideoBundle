<?php
namespace Wtk\VideoBundle\Providers;

use Wtk\VideoBundle\Providers\Provider\AbstractProvider;
use Wtk\VideoBundle\Providers\Provider\Client\Vimeo as VimeoClient;

class Vimeo extends AbstractProvider {

  public function upload($file)
  {
    echo "Uploading $file...";
  }

  /**
   * @return Wtk\VideoBundle\Providers\Provider\Client\Vimeo
   */
  public function getClient()
  {
    return new VimeoClient($this->getConfig());
  }

  /**
   * Retrieve data about movie with give id
   *
   * @param  string $id
   * @return array
   */
  public function get($id)
  {
    /**
     * @var Wtk\VideoBundle\Providers\Provider\Client\Vimeo
     */
    $client = $this->getClient();
    /**
     * Lets make GET request
     * @var RequestInterface
     */
    $request = $client->get();

    $params = array(
      'format' => 'json',
      'method' => 'vimeo.videos.getInfo',
      'video_id'  =>  $id
    );

    foreach($params as $param => $value)
    {
      $request->getQuery()->set($param, $value);
    }

    $response = $request->send();

    if($response->isSuccessful())
    {
      return $response->json();
    }

    /**
     * @todo : How to handle failed attempts?
     */
    return null;
  }
}
