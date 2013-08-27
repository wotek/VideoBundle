<?php

namespace Wtk\VideoBundle\Tests\Service;

use Wtk\VideoBundle\Service\Movies;

class MoviesTest extends \PHPUnit_Framework_TestCase
{
  protected function getRepositoryMock()
  {
    return $this->getMock('Wtk\VideoBundle\Entity\Movie\RepositoryInterface');
  }

  protected function getProviderFactoryMock()
  {
    return $this->getMock('Wtk\VideoBundle\Providers\FactoryInterface');
  }

  protected function getEntityMock()
  {
    return $this->getMock('Wtk\VideoBundle\Entity\Movie');
  }

  protected function getVideoFileMock()
  {
    return $this->getMockBuilder('Wtk\VideoBundle\VideoFile')
    ->disableOriginalConstructor()
    ->getMock();
  }

  protected function getProviderMock()
  {
    return $this->getMock('Wtk\VideoBundle\Providers\Provider\ProviderInterface');
  }

  protected function getServiceStub(array $methods = array(), array $args)
  {
    return $this->getMockBuilder('Wtk\VideoBundle\Service\Movies')
    ->setMethods($methods)
    ->setConstructorArgs($args)
    ->getMock();
  }

  protected function getTestedObject($repository, $factory)
  {
    return new Movies($repository, $factory);
  }

  public function testConstruct()
  {
    // Whoa, what a test ;)
    $this->isInstanceOf(
      'Wtk\VideoBundle\Service\Movies',
      $this->getTestedObject(
        $this->getRepositoryMock(),
        $this->getProviderFactoryMock()
      )
    );
  }

  public function testUpload()
  {
    $provider_name = 'some_implemented_provider';
    $uploaded_id = 1111;
    $file_checksum = 'foobarbaz';

    $file = $this->getVideoFileMock();
    $provider = $this->getProviderMock();
    $factory = $this->getProviderFactoryMock();
    $repository = $this->getRepositoryMock();
    $movie = $this->getEntityMock();

    $repository->expects($this->exactly(2))
    ->method('persist')
    ->with($movie)
    ;

    $repository->expects($this->once())
    ->method('getByChecksum')
    ->with($file_checksum)
    ->will($this->returnValue(false));

    $file->expects($this->exactly(2))
    ->method('getChecksum')
    ->will($this->returnValue($file_checksum));

    $factory->expects($this->once())
    ->method('get')
    ->with($provider_name)
    ->will($this->returnValue($provider));

    $provider->expects($this->once())
    ->method('upload')
    ->with($file)
    ->will($this->returnValue($uploaded_id))
    ;

    $provider->expects($this->once())
    ->method('getId')
    ->will($this->returnValue($provider_name))
    ;

    $movie->expects($this->once())
    ->method('setProvider')
    ->with($provider_name)
    ;

    $movie->expects($this->once())
    ->method('setChecksum')
    ->with($file_checksum)
    ;

    $movie->expects($this->once())
    ->method('setRemoteId')
    ->with($uploaded_id)
    ;

    $movie->expects($this->once())
    ->method('setCompleted')
    ;


    $service = $this->getServiceStub(
      array('getMovie'),
      array(
        $repository,
        $factory
      )
    );

    $service->expects($this->once())
    ->method('getMovie')
    ->will($this->returnValue($movie));

    $this->assertSame(
      $uploaded_id,
      $service->upload($provider_name, $file)
    );
  }

  public function testGetMovieWithProvidedId() {
    $movie_id = 123;

    $repository = $this->getRepositoryMock();
    $movie = $this->getEntityMock();

    $repository->expects($this->once())
                ->method('find')
                ->with($movie_id)
                ->will($this->returnValue($movie));

    $service = $this->getTestedObject(
      $repository, $this->getProviderFactoryMock()
    );

    $this->assertSame($movie, $service->get($movie_id));
  }

  public function testGetWithoutProvidedId() {
    $repository = $this->getRepositoryMock();

    $repository->expects($this->once())
                ->method('findAll')
                ->will($this->returnValue(array()));

    $service = $this->getTestedObject(
      $repository, $this->getProviderFactoryMock()
    );

    $this->assertSame(array(), $service->get());
  }

  public function testGetByChecksum()
  {
    $checksum = 'foobar';
    $movie = $this->getEntityMock();
    $repository = $this->getRepositoryMock();

    $repository->expects($this->once())
                ->method('getByChecksum')
                ->with($checksum)
                ->will($this->returnValue($movie));

    $service = $this->getTestedObject(
      $repository, $this->getProviderFactoryMock()
    );

    $this->assertSame($movie, $service->getByChecksum($checksum));
  }

  public function testSave()
  {
    $movie = $this->getEntityMock();
    $repository = $this->getRepositoryMock();

    $repository->expects($this->once())
                ->method('persist')
                ->with($movie)
                ->will($this->returnValue(true));

    $service = $this->getTestedObject(
      $repository, $this->getProviderFactoryMock()
    );

    $this->assertTrue($service->save($movie));
  }

  public function testGetProvider()
  {
    $provider_name = 'some_implemented_provider';

    $provider = $this->getProviderMock();

    $factory = $this->getProviderFactoryMock();

    $factory->expects($this->once())
                ->method('get')
                ->with($provider_name)
                ->will($this->returnValue($provider));

    $service = $this->getTestedObject(
      $this->getRepositoryMock(), $factory
    );

    $this->isInstanceOf(
      'Wtk\VideoBundle\Providers\Provider\ProviderInterface',
      $service->getProvider($provider_name)
    );
  }


}
