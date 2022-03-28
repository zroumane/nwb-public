<?php

namespace App\Form;

use App\Validator\PasswordContain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ChangePasswordFormType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder->add("plainPassword", RepeatedType::class, [
      "type" => PasswordType::class,
      "first_options" => [
        "attr" => ["autocomplete" => "new-password", "class" => "mb-2"],
        "constraints" => [
          new PasswordContain(),
          new NotBlank([
            "message" => "password.notblank",
          ]),
          new Length([
            "min" => 8,
            "minMessage" => "password.min",
            "max" => 4096,
            "maxMessage" => "password.max",
          ]),
        ],
        "label" => "password.reset.new",
      ],
      "second_options" => [
        "attr" => ["autocomplete" => "new-password"],
        "label" => "password.reset.repeat",
      ],
      "invalid_message" => "password.mustmatch",
      "mapped" => false,
    ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([]);
  }
}
