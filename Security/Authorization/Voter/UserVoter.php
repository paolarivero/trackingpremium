<?php 
// src/NvCarga/Bundle/Security/Authorization/Voter/UserVoter.php
namespace NvCarga\Bundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use NvCarga\Bundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends AbstractVoter
{
   
    const PASSWORD = 'password';
    const SHOW = 'show';

    protected function getSupportedAttributes()
    {
        return array(self::PASSWORD, self::SHOW );
    }

    protected function getSupportedClasses()
    {
        return array('NvCarga\Bundle\Entity\User');
    }

    protected function isGranted($attribute, $userin, $user = null)
    {
        // make sure there is a user object (i.e. that the user is logged in)
        if (!$user instanceof UserInterface) {
            return false;
        }

        // double-check that the User object is the expected entity (this
        // only happens when you did not configure the security system properly)
        if (!$user instanceof User) {
            throw new \LogicException('El usuario NO es de la clase soportada!');
        }

        switch ($attribute) {
            case self::PASSWORD:
                // the data object could have for example a method isPrivate()
                // which checks the Boolean attribute $private
                //if (!$user->isPrivate()) {
                //    return true;
                //}
                if ($userin->getId() === $user->getId()) {
                    return true;
                }
                $roles = $user->getRoles();
                $rolein = ['ROLE_ADMIN','ROLE_ADMIN_USER'];
                foreach ($roles as $rol) {
                    if (in_array($rol->getName(), $rolein)) {
                        return true;
                        break;
                    }
                }
                break;
            case self::SHOW:
                // the data object could have for example a method isPrivate()
                // which checks the Boolean attribute $private
                //if (!$user->isPrivate()) {
                //    return true;
                //}
                if ($userin->getId() === $user->getId()) {
                    return true;
                }
                $roles = $user->getRoles();
                $rolein = ['ROLE_ADMIN','ROLE_ADMIN_USER', 'ROLE_VIEW-USER'];
                foreach ($roles as $rol) {
                    if (in_array($rol->getName(), $rolein)) {
                        return true;
                        break;
                    }
                }
                break;
        }

        return false;
    }
}
?>
