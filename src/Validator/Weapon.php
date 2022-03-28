<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Weapon extends Constraint
{
  public $noWeapon = "At least one weapon expected.";
  public $weaponsDuplicate = "The weapons are duplicate.";
}
