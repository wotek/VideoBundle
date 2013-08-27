<?php
namespace Wtk\VideoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MoviesDetailsCommand extends ContainerAwareCommand
{
  const OPTION_PROVIDER = 'provider';
  const OPTION_MOVIE_ID = 'id';

  protected function configure()
  {
    $this
    ->setName('movies:details')
    ->setDescription('Retrieve movie details from provider')
    ->addOption(
      self::OPTION_PROVIDER,
      null,
      InputOption::VALUE_REQUIRED,
      'Provider name'
      )
    ->addOption(
      self::OPTION_MOVIE_ID,
      null,
      InputOption::VALUE_REQUIRED,
      'Movie id'
      )
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $service = $this->getContainer()->get('wtk.movies');

    $provider = $input->getOption(self::OPTION_PROVIDER);
    $movie_id = (int) $input->getOption(self::OPTION_MOVIE_ID);

    $provider = $service->getProvider($provider);

    $details = $provider->get($movie_id);

    $table = $this->getHelperSet()->get('table');
    $table
        ->setHeaders(
          array('Property', 'Value')
        )
    ;
    /**
     * Flatten nested array
     * @var [type]
     */
    $iterator = new \RecursiveIteratorIterator(
      new \RecursiveArrayIterator($details)
    );

    foreach($iterator as $key => $value ) {
        $table->addRow(array($key, $value));
    }

    $table->render($output);
  }
}
