<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\AppAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class SecurityController extends AbstractController
{
  private $emailVerifier;

  public function __construct(EmailVerifier $emailVerifier)
  {
    $this->emailVerifier = $emailVerifier;
  }

  /**
   * @Route("/login")
   */
  public function login(Request $request, UserPasswordEncoderInterface $passwordEncoder, AuthenticationUtils $authenticationUtils): Response
  {
    if ($this->getUser()) {
      return $this->redirectToRoute("app_profile_index");
    }

    $user = new User();
    $form = $this->createForm(RegistrationFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      // encode the plain password
      $user->setPassword($passwordEncoder->encodePassword($user, $form->get("plainPassword")->getData()));

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($user);
      $entityManager->flush();

      try {
        $this->emailVerifier->sendEmailConfirmation($user);
        $this->addFlash("verify_warning", "login.verify.nocheck");
      } catch (TransportExceptionInterface $e) {
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash("verify_error", "login.verify.noemail");
      }
    }

    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render("security/login.html.twig", [
      
      "registrationForm" => $form->createView(),
      "last_username" => $lastUsername,
      "error" => $error,
    ]);
  }

  /**
   * @Route("/verify-email")
   */
  public function verify_email(Request $request, UserRepository $userRepository, AppAuthenticator $authenticator, GuardAuthenticatorHandler $guardHandler): Response
  {
    $id = $request->get("id");

    if (null === $id) {
      $this->addFlash("verify_error", "login.verify.invalid");
      return $this->redirectToRoute("app_security_login");
    }

    $user = $userRepository->find($id);

    if (null === $user) {
      $this->addFlash("verify_error", "login.verify.invalid");
      return $this->redirectToRoute("app_security_login");
    }

    if ($user->isVerified()) {
      $this->addFlash("verify_error", "login.verify.already");
      return $this->redirectToRoute("app_security_login");
    }

    try {
      $this->emailVerifier->handleEmailConfirmation($request, $user);
    } catch (VerifyEmailExceptionInterface $exception) {
      $this->addFlash("verify_error", $exception->getReason());
      return $this->redirectToRoute("app_security_login");
    }

    $this->addFlash("verify_succes", "login.verify.check");

    return $guardHandler->authenticateUserAndHandleSuccess($user, $request, $authenticator, "main");
  }

  /**
   * @Route("/logout")
   */
  public function logout()
  {
    throw new \LogicException();
  }
}
