<?php

namespace App\Serializer;

use App\Entity\User;

interface UserOwnedInterface
{
  public function getAuthor(): ?User;

  public function setAuthor(?User $author): self;
}