<?php
namespace Wtk\VideoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Wtk\VideoBundle\Entity\Movie;
use Wtk\VideoBundle\Providers\Factory as ProviderFactory;
use Wtk\VideoBundle\VideoFile;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;


class MoviesCommand extends ContainerAwareCommand
{
  const OPTION_PROVIDER = 'provider';
  const OPTION_PATH = 'path';
  const OPTION_TITLE = 'title';
  const OPTION_DESCRIPTION = 'description';

  /**
   * @return void
   */
  protected function configure()
  {
    $this
    ->setName('movies:upload')
    ->setDescription('Upload video file to one of supported providers')
    ->addOption(
      self::OPTION_PROVIDER,
      null,
      InputOption::VALUE_REQUIRED,
      'Provider name'
      )
    ->addOption(
      self::OPTION_PATH,
      null,
      InputOption::VALUE_REQUIRED,
      'Path to file'
      )
    ->addOption(
      self::OPTION_TITLE,
      null,
      InputOption::VALUE_OPTIONAL,
      'Uploaded video title'
      )
    ->addOption(
      self::OPTION_DESCRIPTION,
      null,
      InputOption::VALUE_OPTIONAL,
      'Video description'
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
    /**
     * @var Wtk\VideoBundle\Service\Movies
     */
    $service = $this->getContainer()->get('wtk.movies');

    list($provider, $filepath) = $this->parseOptions($input->getOptions());

    $errors = $this->checkFile($filepath);

    if(0 < count($errors))
    {
      foreach($errors as $error)
      {
        $this->error($output, $error->getMessage());
      }
      return false;
    }

    /**
     * For verbose purposes only.
     * // if --verbose option?
     */
    ProviderFactory::registerLogger($output);
    ProviderFactory::registerProgressHelper(
      $this->getHelperSet()->get('progress'),
      $output
    );


    /**
     * Upload file using $provider
     */
    $service->upload($provider, new VideoFile($filepath), $output);
  }

  /**
   * @param  string     $filepath
   * @throws Exception
   *
   * @return ConstraintViolationListInterface
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
      'mimeTypesMessage' => 'Please select a valid video file',
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
