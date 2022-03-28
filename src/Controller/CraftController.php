<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @return Response
 */
class CraftController extends AbstractController
{

  /**
   * @Route("/craft")
   */
  // public function index(): Response
  // {
  //   return $this->render("craft/index.html.twig");
  // }

  /**
   * @Route("/admin/craft")
   */
  public function admin(): Response
  {
    return $this->render("craft/admin.html.twig");
  }
}
