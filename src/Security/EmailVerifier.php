<?php

namespace App\Security;

use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class EmailVerifier
{
  private $verifyEmailHelper;
  private $mailer;
  private $entityManager;

  public function __construct(VerifyEmailHelperInterface $helper, MailerInterface $mailer, EntityManagerInterface $manager)
  {
    $this->verifyEmailHelper = $helper;
    $this->mailer = $mailer;
    $this->entityManager = $manager;
  }

  public function sendEmailConfirmation(UserInterface $user): void
  {
    $user_email = $user->getEmail();
    $user_id = $user->getId();

    $email = new TemplatedEmail();
    $email->from(new Address("noreply@newworld-builder.com", "NewWorld-Builder.com"));
    $email->to($user_email);
    $email->subject("NewWorld-Builder.com Email Verification");
    $email->htmlTemplate("security/mail.confirm.twig");

    $signatureComponents = $this->verifyEmailHelper->generateSignature("app_security_verify_email", $user_id, $user_email, ["id" => $user_id]);

    $context = $email->getContext();
    $context["signedUrl"] = $signatureComponents->getSignedUrl();
    $context["expiresAtMessageKey"] = $signatureComponents->getExpirationMessageKey();
    $context["expiresAtMessageData"] = $signatureComponents->getExpirationMessageData();
    $context["pseudo"] = $user->getPseudo();

    $email->context($context);

    $this->mailer->send($email);
  }

  /**
   * @throws VerifyEmailExceptionInterface
   */
  public function handleEmailConfirmation(Request $request, UserInterface $user): void
  {
    $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());

    $user->setIsVerified(true);

    $this->entityManager->persist($user);
    $this->entityManager->flush();
  }
}
