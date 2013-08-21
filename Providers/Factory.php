<?php
namespace Wtk\VideoBundle\Providers;

/**
 * @author wzalewski
 */
class Factory {

  const VIMEO = 'vimeo';
  const YOUTUBE = 'youtube';

  /**
   * Available providers
   * @var array
   */
  protected static $providers = array(
    self::VIMEO,
    self::YOUTUBE,
  );

  /**
   * @param  string $provider
   * @param  array  $config
   * @return ProviderInterface
   */
  public static function factory($provider, array $config = array())
  {
    if(false === self::validateProvider($provider))
    {
      throw new \InvalidArgumentException(
        sprintf("Invalid provider given. Supported providers: %s",
          implode(', ', $this->providers)
        )
      );
    }

    $providerClassName = self::resolveProviderClassname($provider);

    if(!class_exists($providerClassName))
    {
      throw new \InvalidArgumentException(
        "Class $providerClassName does not exists"
      );
    }

    return new $providerClassName($config);
  }

  /**
   * @param  string $provider
   * @return bool
   */
  public static function validateProvider($provider)
  {
    return in_array($provider, self::$providers);
  }

  /**
   * @param  string $provider
   * @return string
   */
  protected static function resolveProviderClassname($provider)
  {
    return __NAMESPACE__ . '\\' . ucfirst($provider);
  }

  // Well, I could not find way to tell DI container to call
  // factory method statically :(
  //
  // @todo: Dig through docs/code
  //
  // private function __construct()
  // {}

  public function __clone()
  {}
}
