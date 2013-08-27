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

    $file = new VideoFile($filepath);

    /**
     * Set file title & description.
     * Provider will discover that file has title & description provided.
     * If so. Will notify remote API.
     */
    $file->setTitle($input->getOption(self::OPTION_TITLE));
    $file->setDescription($input->getOption(self::OPTION_DESCRIPTION));

    // $this->progress = $this->getHelperSet()->get('progress');
    // $this->progress->start($output, $file->getSize());
    // $this->upload($service->getProvider($provider), $file);

    $this->log(
      sprintf(
        "Uploading %s this might take a while. Hold on. Go get a coffe",
        $file->getFilename()
      )
    );
    $video_id = $service->upload($provider, $file);
    $this->log("File id: $video_id uploaded.");
  }

  /**
   * This method is not actually needed. You could simply use
   * service method to upload file.
   *
   * For verbosity & debugging reasons
   * the code is duplicated here to take advantage of
   * Command output interface & helpers.
   *
   * @param  ProviderInterface     $provider
   * @param  VideoFile             $file
   * @return int
   */
  protected function upload(ProviderInterface $provider, VideoFile $file)
  {
    $this->log(
      sprintf("Uploading file %s ...", $file->getFilename())
    );

    $client = $provider->getClient();
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
    // if($this->progress)
    // {
    //   $helper = $this->progress;
    //   $progress_callback = function($event) use ($helper)
    //   {
    //     // We'll get > 100%. EntityBody payload. Dont worry ;)
    //     $helper->advance($event['length']);
    //   };
    // }

    $is_success = $client->upload($ticket['endpoint'], $file, $progress_callback);

    if(false === $is_success)
    {
      throw new ProviderException("File upload failed");
    }

    /**
     * 4. Verfiy upload
     */
    $verified = $client->verify($ticket['endpoint'], $file);
    $this->log(sprintf("Upload verified?: %s", $verified ? 'Yes' : 'No'));

    if(false === $verified)
    {
      throw new ProviderException("Cannot verify uploaded file.");
    }

    $video_id = (int) $client->complete($ticket['id'], $file->getFilename());

    $this->log(sprintf("Uploaded video id : %d", $video_id));

    if($file->getTitle()){
      $provider->setTitle($video_id, $file->getTitle());
    }

    if($file->getDescription()){
      $provider->setDescription($video_id, $file->getDescription());
    }

    return $video_id;
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
