<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Characteristics extends Constraint
{
  public $invalidNumeric = "One number is invalid.";
  public $tooMuchPoint = "Too much point.";
}
