<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class JsonArrayLenghtValidator extends ConstraintValidator
{
  public function validate($value, Constraint $constraint)
  {

    if (!$constraint instanceof JsonArrayLenght) {
      throw new UnexpectedTypeException($constraint, JsonArrayLenght::class);
    }

    if (count($value) != $constraint->lenght['value']) {
      $this->context->buildViolation($constraint->message)->addViolation();
    }
  }
}
