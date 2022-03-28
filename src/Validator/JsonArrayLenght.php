<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class JsonArrayLenght extends Constraint
{
  public $lenght;
  public $message = "This json array has not the valid lenght.";
  // public $messageblank = "The values should not be blank.";

  public function __construct($lenght = 2) {
    $this->lenght = $lenght ?? $this->lenght;
  }

}
