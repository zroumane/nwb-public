<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PasswordContainValidator extends ConstraintValidator
{
  public function validate($value, Constraint $constraint)
  {
    if (!$constraint instanceof PasswordContain) {
      throw new UnexpectedTypeException($constraint, PasswordContain::class);
    }

    if (null === $value || "" === $value) {
      return;
    }

    if (!is_string($value)) {
      throw new UnexpectedValueException($value, "string");
    }

    if (!preg_match("/[a-z]/", $value, $matches)) {
      $this->context->buildViolation($constraint->messageLower)->addViolation();
    }

    if (!preg_match("/[A-Z]/", $value, $matches)) {
      $this->context->buildViolation($constraint->messageUpper)->addViolation();
    }

    if (!preg_match("/[0-9]/", $value, $matches)) {
      $this->context->buildViolation($constraint->messageDigit)->addViolation();
    }
  }
}
