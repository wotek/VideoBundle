<?php
namespace Wtk\VideoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Wtk\VideoBundle\Entity\Movie;
use Wtk\VideoBundle\Providers\Factory as ProviderFactory;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;


class MoviesCommand extends ContainerAwareCommand
{
  const OPTION_PROVIDER = 'provider';
  const OPTION_PATH = 'path';

  /**
   * @return void
   */
  protected function configure()
  {
    $this
    ->setName('movies:upload')
    ->setDescription('Upload video file to one of supported providers')
    ->addOption(
      'provider',
      null,
      InputOption::VALUE_REQUIRED,
      'Which provider: youtube, vimeo'
      )
    ->addOption(
      'path',
      null,
      InputOption::VALUE_REQUIRED,
      'Path to file'
      )
    ;
  }

  /**
   * @param  InputInterface  $input
   * @param  OutputInterface $output
   * @return void
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $service = $this->getContainer()->get('wtk.movies');

    list($provider, $filepath) = $this->parseOptions($input->getOptions());

    if(false === ProviderFactory::validateProvider($provider))
    {
      throw new \Exception("Invalid provider given.");
    }

    $provider = $this->getContainer()->get("wtk.movies.provider.$provider");

    $errors = $this->checkFile($filepath);
    if(0 < count($errors))
    {
      foreach($errors as $error)
      {
        $this->error($output, $error->getMessage());
      }
      return false;
    }

    $file = new File($filepath);

    $service->setProvider($provider);
    $service->upload($file);
  }

  /**
   * @param  string     $filepath
   * @throws Exception
   * @return ConstraintViolationListInterface
   *
   * @todo : When implementing controller, move it to service.
   */
  protected function checkFile($file)
  {
    $validator = Validation::createValidator();

    $constraint = new Assert\File(array(
      'mimeTypes' => array(
        'video/x-msvideo',
        'video/quicktime',
        'video/mpeg',
        'video/mp4',
        // Feel free to add more types.
        // This is just a proof of concept
      ),
      'mimeTypesMessage' => 'Please upload a valid video file',
    ));

    return $validator->validateValue($file, $constraint);
  }

  /**
   * @param  array  $options
   * @return array
   */
  protected function parseOptions(array $options)
  {
    return array(
      strtolower($options[self::OPTION_PROVIDER]),
      $options[self::OPTION_PATH]
    );
  }

  /**
   * Helper
   * @param  OutputInterface $output
   * @param  string          $message
   * @return void
   */
  protected function error(OutputInterface $output, $message)
  {
    $output->writeln(sprintf('<error>%s</error>', $message));
  }
}
