<?php

namespace App\Controller;

use App\Entity\Build;
use App\Entity\EntityParser;
use App\Repository\BuildRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\WeaponRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @return Response
 */
class BuildsController extends AbstractController
{

  public function checkPermission($build){
    return $build->getAuthor() == $this->getUser() or $this->getUser() ? in_array('ROLE_BUILD_ADMIN', $this->getUser()->getRoles()) : false;
  }

  /**
   * @Route("/")
   */
  public function index(Request $request, PaginatorInterface $paginator, BuildRepository $buildRep, WeaponRepository $weaponRep): Response
  {
    $query = $buildRep->findAllQuery($request->query, $this->getUser() ?? null);
    $builds = $paginator->paginate($query, $request->query->get('p') ?? 1, 20);
    $parser = new EntityParser();
    $parser->setWeaponLocal($request->getLocale(), $this->getParameter('kernel.project_dir'));
    $parser->setWeapons($weaponRep->findAll());
    $builds->setItems(array_map(fn($build) => $parser->parseBuild($build), (array)$builds->getItems()));
    return $this->render("build/index.html.twig", [
      "builds" => $builds,
      "weapons" => $parser->getWeapons()
    ]);

  }

  /**
   * @Route("/admin/weapon")
   */
  public function weapon(): Response
  {
    return $this->render("build/weapon.html.twig");
  }

  /**
   * @Route("/admin/build")
   */
  public function admin(): Response
  {
    return $this->render("build/admin.html.twig");
  }

  /**
   * @Route("/build/{id}", requirements={"id"="\d+"})
   */
  public function show(Build $build): Response
  {
    $session = $this->get('session');
    $sessionViews = $session->get('views');
    $buildId = $build->getId();
    
    if(!in_array($buildId, $sessionViews)){
      $views = $build->getViews();
      $build->setViews($views + 1);
      $build->setNotSendDiscord(true);
      $em = $this->getDoctrine()->getManager();
      $em->flush();
      array_push($sessionViews, $buildId);
      $session->set('views', $sessionViews);
    }
    
    return $this->render("build/build.html.twig", [
      "build" => $build,
      "favorite" => $build->getFavorites()->contains($this->getUser() ?? null)
    ]);
  }


  /**
   * @Route("/build/{id}/fav", requirements={"id"="\d+"})
   */
  public function fav(Build $build): Response
  {
    if($user = $this->getUser()){
      $build->addFavorites($user);
      $build->setNotSendDiscord(true);
      $em = $this->getDoctrine()->getManager();
      $em->flush();
    }

    return $this->redirectToRoute('app_builds_show', ['id' => $build->getId()]);
  }

  /**
   * @Route("/build/{id}/unfav", requirements={"id"="\d+"})
   */
  public function unfav(Build $build): Response
  {
    if($user = $this->getUser()){
      $build->removeFavorites($user);
      $build->setNotSendDiscord(true);
      $em = $this->getDoctrine()->getManager();
      $em->flush();
    }

    return $this->redirectToRoute('app_builds_show', ['id' => $build->getId()]);
  }

  /**
   * @Route("/create")
   */
  public function create(): Response
  {
    return $this->render("build/create.html.twig");
  }

  /**
   * @Route("/edit/{id}", requirements={"id"="\d+"})
   */
  public function edit(Build $build): Response
  {
    if($build && $this->checkPermission($build)){
      return $this->render("build/create.html.twig", [
        "build" => $build
      ]);
    }
    throw $this->createNotFoundException();
  }

    /**
   * @Route("/visibility/{id}/{visibility}", requirements={"id"="\d+","visibility"="[0-1]"})
   */
  public function visibility(Build $build, $visibility, Request $request): Response
  {
    if($build && $this->checkPermission($build)){
      $build->setPrivate($visibility);
      $build->setNotSendDiscord(true);
      $em = $this->getDoctrine()->getManager();
      $em->flush();
      return $this->redirectToRoute("app_profile_index");
    }
    throw $this->createAccessDeniedException();
  }

  /**
   * @Route("/delete/{id}", requirements={"id"="\d+"})
   */
  public function delete(Build $build): Response
  {
    if($build && $this->checkPermission($build)){
      return $this->render("build/delete.html.twig", [
        "build" => $build
      ]);
    }
    throw $this->createAccessDeniedException();
  }

  /**
   * @Route("/delete/{id}/confirm", requirements={"id"="\d+"}, methods={"POST"})
   */
  public function confirm_delete(Build $build, Request $request): Response
  {
    if ($this->isCsrfTokenValid('delete'.$build->getId(), $request->request->get('_token'))) {
      if($build && $this->checkPermission($build)){
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($build);
        $entityManager->flush();
        return $this->redirectToRoute('app_profile_index');
      }
    }
    throw $this->createAccessDeniedException();
  }
}
