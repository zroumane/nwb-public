<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PseudoRegexValidator extends ConstraintValidator
{
  public function validate($value, Constraint $constraint)
  {
    if (!$constraint instanceof PseudoRegex) {
      throw new UnexpectedTypeException($constraint, PseudoRegex::class);
    }

    if (null === $value || "" === $value) {
      return;
    }

    if (!is_string($value)) {
      throw new UnexpectedValueException($value, "string");
    }

    if (preg_match("/[^A-Za-z0-9]/", $value, $matches)) {
      if (count($matches) > 0) {
        $this->context->buildViolation($constraint->message)->addViolation();
      }
    }
  }
}
