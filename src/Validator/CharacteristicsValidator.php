<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CharacteristicsValidator extends ConstraintValidator
{

  public function validate($characteristics, Constraint $constraint)
  {

    if (!$constraint instanceof Characteristics) {
      throw new UnexpectedTypeException($constraint, Characteristics::class);
    }

    $sum = 0;

    foreach ($characteristics[1] as $car => $value) {
      if (!is_numeric($value) || $value < 0){
        $this->context->buildViolation($constraint->invalidNumeric)->addViolation();
      }else{
        $sum += $value;
      }
    }

    if (190 - $sum != $characteristics[0] || $characteristics[0] > 190 || $characteristics[0] < 0){
      $this->context->buildViolation($constraint->tooMuchPoint)->addViolation();
    }

    foreach ($characteristics[2] as $k => $value) {
      if (!is_numeric($value) || $value < 0 || $value > 1000){
        $this->context->buildViolation($constraint->invalidNumeric)->addViolation();
      }
    }

  }
}
