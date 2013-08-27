<?php
namespace Wtk\VideoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Wtk\VideoBundle\Entity\Movie;
use Wtk\VideoBundle\Providers\Provider\ProviderInterface;
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
   * @var OutputInterface
   */
  protected $output;

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
    $this->output = $output;
    /**
     * @var Wtk\VideoBundle\Service\Movies
     */
    $service = $this->getContainer()->get('wtk.movies');

    list($provider, $filepath, $title, $description) =
      $this->parseOptions($input->getOptions());

    $errors = $this->checkFile($filepath);

    if(0 < count($errors))
    {
      foreach($errors as $error)
      {
        $this->error($output, $error->getMessage());
      }
      return false;
    }

    $file = new VideoFile($filepath);
    /**
     * Set file title & description.
     *
     * Provider will discover that file has title & description provided.
     *
     * If so. Will notify remote API.
     */
    $file->setTitle($title);
    $file->setDescription($description);

    /**
     * Idea: What if provider implemented EventDispatcherInterface
     * and might want to notify what's he up to right now?
     *
     * @todo : Make IoEmittingVimeo provider. This is the cleanest way to
     *         implement verbosity (yep, I'm thinking it now ;>)
     *         without making code look like crap.
     */
    $this->log(sprintf(
        "Uploading %s this might take a while. Hold on. Go get a coffee",
        $file->getFilename())
    );

    $video_id = $service->upload($provider, $file);

    $this->log("File id: $video_id uploaded.");
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
      $options[self::OPTION_PATH],
      $options[self::OPTION_TITLE],
      $options[self::OPTION_DESCRIPTION]
    );
  }

  /**
   * Helper
   * @param  string          $message
   * @return void
   */
  protected function error($message)
  {
    $this->output->writeln(sprintf('<error>%s</error>', $message));
  }

  /**
   * @param  string $message
   * @return void
   */
  protected function log($message)
  {
    $this->output->writeln(sprintf("<info>%s</info>", $message));
  }
}
