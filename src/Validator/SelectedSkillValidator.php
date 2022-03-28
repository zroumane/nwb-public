<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use ApiPlatform\Core\Api\IriConverterInterface;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;


class SelectedSkillValidator extends ConstraintValidator{

  private $iriConverter;

  public function __construct(IriConverterInterface $iriConverter){
    $this->iriConverter = $iriConverter;
  }

  public function validate($selectedSkills, Constraint $constraint)
  {

    if (!$constraint instanceof SelectedSkill) {
      throw new UnexpectedTypeException($constraint, SelectedSkill::class);
    }
    
    /**
     * Iteration weapon 
     */
    foreach($selectedSkills as $weaponIndex => $weaponSelectedSkills) {
      $weaponIRI = $this->context->getObject()->getWeapons()[$weaponIndex];
      if(!$weaponIRI){
        continue;
      }
      $weaponItem = $this->iriConverter->getItemFromIri($weaponIRI);
      $weaponKey = $weaponItem->getWeaponKey();
      $weaponBranches = $weaponItem->getBranch();
      $weaponSelectedSkillsItem = [];
      

      if(count($weaponSelectedSkills) < 0){
        $this->context
          ->buildViolation($constraint->toMuchSkill)
          ->setParameter('{{ weapon }}', $weaponKey)
          ->addViolation();
      }
      
      /**
       * Iteration skill de l'arme 
       * Check si skill existe
       * Récupère l'item correspondant à l'iri
       */
      foreach ($weaponSelectedSkills as $index => $selectedSkill) {
        try {
          $weaponSelectedSkillsItem[$index] = $this->iriConverter->getItemFromIri($selectedSkill);
          //TODO, check doublon
        } catch (\Throwable $th) {
          $weaponSelectedSkillsItem[$index] = null;
          $this->context
            ->buildViolation($constraint->notFound)
            ->setParameter('{{ skill }}', $selectedSkill)
            ->addViolation();
        }
      }

      /**
       * Compte le nombre de skill par side
       */
      $sideCounter = [
        count(array_filter($weaponSelectedSkillsItem, function($s){
          return $s && $s->getSide() == 1;
        })),
        count(array_filter($weaponSelectedSkillsItem, function($s){
          return $s && $s->getSide() == 2;
        }))
      ];

      /**
       * Iteration skill
       */
      foreach ($weaponSelectedSkills as $index => $selectedSkill) {
        $selectedSkillItem = $weaponSelectedSkillsItem[$index];
        if($selectedSkillItem){
          $skillKey = $selectedSkillItem->getSkillKey();
          $skillLine = $selectedSkillItem->getLine();
          $skillSide = $selectedSkillItem->getSide();
          
          /**
           * Check si skill dans weapon
           */
          if(!$weaponItem->getSkills()->contains($selectedSkillItem)){
            $this->context
            ->buildViolation($constraint->notInWeapon)
            ->setParameter('{{ skill }}', $skillKey)
            ->setParameter('{{ weapon }}', $weaponKey)
            ->addViolation();
            continue;
          }
           
          /**
           * Check doublon
           */
          if(count(array_filter($weaponSelectedSkills, function($s) use ($selectedSkill){
            return $s == $selectedSkill;
          })) > 1){
            $this->context
              ->buildViolation($constraint->skillDuplicate)
              ->setParameter('{{ skill }}', $skillKey)
              ->addViolation();
            continue;
          }

          /**
           * Si parent, check si selected
           */
          $parent = $selectedSkillItem->getParent();
          if($parent){
            if(!in_array($this->iriConverter->getIriFromItem($parent), $weaponSelectedSkills)){
              $this->context
                ->buildViolation($constraint->parentNotSelected)
                ->setParameter('{{ skill }}', $skillKey)
                ->setParameter('{{ parent }}', $parent->getSkillKey())
                ->addViolation();
            }
          }

          /**
           * Check si skill selected ligne supérieur
           */
          if($skillLine != 1){                    
            if(count(array_filter($weaponSelectedSkillsItem, function($s) use ($skillLine, $skillSide){ 
              return $s && $s->getLine() == $skillLine - 1 && $s->getSide() == $skillSide;
            })) == 0){
              $this->context
              ->buildViolation($constraint->aboveLineNoSelect)
              ->setParameter('{{ skill }}', $skillKey)
              ->addViolation();
            }
          }

          /**
           * Si ligne 6, check si déja 10 skills selected dans le side
           */
          if($skillLine == 6){
            if($sideCounter[$skillSide - 1] <= 10){
              $this->context
                ->buildViolation($constraint->sideLenght)
                ->setParameter('{{ branch }}', $weaponBranches[$skillSide - 1])
                ->setParameter('{{ skill }}', $skillKey)
                ->addViolation();
            }
          }
        }
      }
    }
  }
}
