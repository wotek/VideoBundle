<?php

namespace Wtk\VideoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WtkVideoBundle:Default:index.html.twig', array('name' => $name));
    }
}
