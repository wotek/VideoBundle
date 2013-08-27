<?php
namespace Wtk\VideoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class MoviesListCommand extends ContainerAwareCommand
{
  protected function configure()
  {
    $this
    ->setName('movies:list')
    ->setDescription('Lists movies repository')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $service = $this->getContainer()->get('wtk.movies');

    $normalizer = new GetSetMethodNormalizer();
    $serializer = new Serializer(array($normalizer));

    $movies = $service->get();

    $table = $this->getHelperSet()->get('table');
    $table
        ->setHeaders(array('ID', 'RemoteID', 'Checksum', 'Provider', "Completed"))
    ;

    foreach ($movies as $movie) {
      $table->addRow($serializer->normalize($movie));
    }

    $table->render($output);

  }
}
