<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PseudoRegex extends Constraint
{
  public $message = "pseudo.regex";
}
