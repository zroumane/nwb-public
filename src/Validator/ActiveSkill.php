<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ActiveSkill extends Constraint
{
  public $Lenght = "Array lenght is wrong.";
  public $NotAbility = "{{ skill }} is not an ability.";
  public $SkillDuplicate = "{{ skill }} is duplicated.";
  public $NotFound = "{{ skill }} not found.";
  public $NotActived = "{{ skill }} is not selected.";
}
