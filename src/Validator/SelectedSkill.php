<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SelectedSkill extends Constraint
{
  public $toMuchSkill = 'Too much for weapon "{{ weapon }}".';
  public $notInWeapon = '"{{ skill }}" not belong to weapon "{{ weapon }}".';
  public $notFound = '"{{ skill }}" not found.';
  public $skillDuplicate = '"{{ skill }}" is duplicate.';
  public $aboveLineNoSelect = 'Line above skill "{{ skill }}" has no selected skills.';
  public $parentNotSelected = 'Parent "{{ parent }}" for {{ skill }} is not selected.';
  public $sideLenght = 'Not enought for branch "{{ branch }}" to select "{{ skill }}".';
}