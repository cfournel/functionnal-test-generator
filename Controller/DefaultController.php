<?php

namespace huitiemesens\FunctionalTestGeneratorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('FunctionalTestGeneratorBundle:Default:index.html.twig', array('name' => $name));
    }
}
