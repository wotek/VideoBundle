<?php
namespace Wtk\VideoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MoviesAPITestingCommand extends ContainerAwareCommand
{
  protected function configure()
  {
    $this
    ->setName('movies:api')
    ->setDescription('API testing playground')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $service = $this->getContainer()->get('wtk.movies');
    $vimeo = $service->getProvider('vimeo')->getClient();

    $response = $vimeo->checkTicket('3067758443a5729bacb60739ae69bfaf');

    var_export($response);

  }
}
