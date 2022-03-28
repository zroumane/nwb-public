<?php
namespace App\Security;

use App\Entity\User;
use App\Entity\Build;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CheckUserBuild extends Voter
{
    const CHECK_USER_BUILD = 'CheckUserBuild';

    protected function supports($attribute, $subject)
    {      
      
      if (!$subject instanceof Build) {
        return false;
      }

      
      if (!in_array($attribute, array(self::CHECK_USER_BUILD))) {
        return false;
      }

      return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {

        $user = $token->getUser();
        if(!$user instanceOf User) {
            return false;
        }

        if($subject->getAuthor() === $user) {
            return true;
        }

        return false;
    }
}