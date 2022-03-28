<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PasswordContain extends Constraint
{
  public $messageLower = "password.lower";
  public $messageUpper = "password.upper";
  public $messageDigit = "password.digit";
}
