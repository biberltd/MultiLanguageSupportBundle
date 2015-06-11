<?php

namespace BiberLtd\Bundle\MultiLanguageSupportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BiberLtdMultiLanguageSupportBundle:Default:index.html.twig', array('name' => $name));
    }
}
