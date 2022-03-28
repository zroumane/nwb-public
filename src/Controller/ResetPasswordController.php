<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Mime\Address;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
  use ResetPasswordControllerTrait;

  private $resetPasswordHelper;

  public function __construct(ResetPasswordHelperInterface $resetPasswordHelper)
  {
    $this->resetPasswordHelper = $resetPasswordHelper;
  }

  /**
   * Display & process form to request a password reset.
   */
  #[Route('', name: 'app_password_forgot')]
  public function request(Request $request, MailerInterface $mailer, $send = false): Response
  {
    if ($request->query->get("send") == 1) {
      $send = true;
    }

    $form = $this->createForm(ResetPasswordRequestFormType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      return $this->processSendingPasswordResetEmail($form->get("email")->getData(), $mailer);
    }

    return $this->render("security/pass.request.html.twig", [
      "requestForm" => $form->createView(),
      "send" => $send,
    ]);
  }

  /**
   * Validates and process the reset URL that the user clicked in their email.
   */
  #[Route('/reset/{token}', name: 'app_password_reset')]
  public function reset(Request $request, UserPasswordEncoderInterface $passwordEncoder, string $token = null): Response
  {
    if ($token) {
      // We store the token in session and remove it from the URL, to avoid the URL being
      // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
      $this->storeTokenInSession($token);

      return $this->redirectToRoute("app_password_reset");
    }

    $token = $this->getTokenFromSession();
    if (null === $token) {
      throw $this->createNotFoundException("No reset password token found in the URL or in the session.");
    }

    try {
      $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
    } catch (ResetPasswordExceptionInterface $e) {
      $this->addFlash("reset_password_error", sprintf("There was a problem validating your reset request - %s", $e->getReason()));

      return $this->redirectToRoute("app_password_forgot");
    }

    // The token is valid; allow the user to change their password.
    $form = $this->createForm(ChangePasswordFormType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      // A password reset token should be used only once, remove it.
      $this->resetPasswordHelper->removeResetRequest($token);

      // Encode the plain password, and set it.
      $encodedPassword = $passwordEncoder->encodePassword($user, $form->get("plainPassword")->getData());

      $user->setPassword($encodedPassword);
      $this->getDoctrine()
        ->getManager()
        ->flush();

      // The session is cleaned up after the password has been changed.
      $this->cleanSessionAfterReset();

      return $this->redirectToRoute("app_security_login");
    }

    return $this->render("security/pass.reset.html.twig", [
      "resetForm" => $form->createView(),
    ]);
  }

  private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
  {
    $user = $this->getDoctrine()
      ->getRepository(User::class)
      ->findOneBy([
        "email" => $emailFormData,
      ]);

    // Do not reveal whether a user account was found or not.
    if (!$user) {
      return $this->redirectToRoute("app_password_forgot", ["send" => true]);
    }

    try {
      $resetToken = $this->resetPasswordHelper->generateResetToken($user);
    } catch (ResetPasswordExceptionInterface $e) {
      return $this->redirectToRoute("app_password_forgot");
    }

    $email = (new TemplatedEmail())
      ->from(new Address("noreply@newworld-builder.com", "NewWorld-Builder.com"))
      ->to($user->getEmail())
      ->subject("Your password reset request")
      ->htmlTemplate("security/mail.reset.html.twig")
      ->context([
        "resetToken" => $resetToken,
      ]);

    $mailer->send($email);

    // Store the token object in session for retrieval in check-email route.
    $this->setTokenObjectInSession($resetToken);

    return $this->redirectToRoute("app_password_forgot", ["send" => true]);
  }
}
