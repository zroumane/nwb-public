<?php

namespace App\Serializer;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;

class UserOwnedDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{

  use DenormalizerAwareTrait;

  public function __construct(private Security $security)
  {
  }

  private const ALREADY_CALLED_DENORMALIZER = 'UserOwnedDenormalizerCalled';

  public function supportsDenormalization($data, string $type, ?string $format = null, array $context = [])
  {
    $reflectionClass = new \ReflectionClass($type);
    $alreadyCalled = $context[self::ALREADY_CALLED_DENORMALIZER] ?? false;
    return $reflectionClass->implementsInterface(UserOwnedInterface::class) && $alreadyCalled === false;
  }
  
  public function denormalize($data, string $type, ?string $format = null, array $context = [])
  {
    $context[self::ALREADY_CALLED_DENORMALIZER] = true;
    $obj = $this->denormalizer->denormalize($data, $type, $format, $context);
    if (key_exists('collection_operation_name', $context)){
      $obj->setAuthor($this->security->getUser());
    }else{
      if(in_array('ROLE_BUILD_ADMIN', $this->security->getUser()->getRoles()) && $obj->getAuthor() != $this->security->getUser()){
        $obj->setNotSendDiscord(true);
      }
    }

    return $obj;
  }
  
}
