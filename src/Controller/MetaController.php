<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MetaController extends AbstractController
{
  /**
   * @Route("/privacy")
   */
  public function privacy(): Response
  {
    return $this->render("meta/privacy.html.twig");
  }

    /**
   * @Route("/contact")
   */
  public function contact(): Response
  {
    return $this->render("meta/contact.html.twig");
  }
}
