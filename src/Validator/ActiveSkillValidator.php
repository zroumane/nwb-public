<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use ApiPlatform\Core\Api\IriConverterInterface;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;


class ActiveSkillValidator extends ConstraintValidator
{

  private $iriConverter;

  public function __construct(IriConverterInterface $iriConverter)
  {
    $this->iriConverter = $iriConverter;
  }

  public function validate($activedSkills, Constraint $constraint)
  {

    if (!$constraint instanceof ActiveSkill) {
      throw new UnexpectedTypeException($constraint, ActiveSkill::class);
    }


    $selectedSkills = $this->context->getObject()->getSelectedSkills();

    /**
     * Iteration weapon
     */
    foreach($activedSkills as $weaponIndex => $weaponactivedSkills) {
      $weaponSelectedSkills = $selectedSkills[$weaponIndex];
      
      /**
       * Check active skill array lenght
       */
      if(count($weaponactivedSkills) != 3){
        $this->context->buildViolation($constraint->Lenght)->addViolation();
      }

      /**
       * Iteration active skill
       */
      foreach ($weaponactivedSkills as $key => $activedSkill) {
        
        /**
         * Pass si pas de skill
         */
        if($activedSkill == null){
          continue;
        }

        /**
         * Check si skill est un skill ability
         */
        try {
          $activedSkillItem = $this->iriConverter->getItemFromIri($activedSkill);
          if($activedSkillItem->getType() != 1){
            $this->context->buildViolation($constraint->NotAbility)->addViolation();
          }

          /**
           * Check doublon
           */
          if(count(array_filter($weaponactivedSkills, function($s) use ($activedSkill){
            return $s == $activedSkill;
          })) > 1){
            $this->context
              ->buildViolation($constraint->SkillDuplicate)
              ->setParameter('{{ skill }}', $activedSkillItem->getSkillKey())
              ->addViolation();
            continue;
          }


        } catch (\Throwable $th) {
          $this->context->buildViolation($constraint->NotFound)->addViolation();
        }

        /**
         * Check si skill selected
         */
        if(!in_array($activedSkill, $weaponSelectedSkills)){
          $this->context->buildViolation($constraint->NotActived)->addViolation();
        }

      }
    }
  }
}
