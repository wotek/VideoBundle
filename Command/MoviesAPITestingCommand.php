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
    $yt = $this->getContainer()->get('wtk.movies.provider.vimeo');
    $video = $yt->get(2);
    var_export($video);

    // $yt = $this->getContainer()->get('wtk.movies.provider.youtube');
    // $video = $yt->get('the0KZLEacs');
  }
}
