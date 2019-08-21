<?php 
// src/NvCarga/Bundle/Security/Authorization/Voter/UserVoter.php
namespace NvCarga\Bundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use NvCarga\Bundle\Entity\User;
use NvCarga\Bundle\Entity\Pobox;
use Symfony\Component\Security\Core\User\UserInterface;
use NvCarga\Bundle\Entity\Role;

class PoboxVoter extends AbstractVoter
{
   
    const SHOW = 'show';

    protected function getSupportedAttributes()
    {
        return array(self::SHOW);
    }

    protected function getSupportedClasses()
    {
        return array('NvCarga\Bundle\Entity\Pobox');
    }

    protected function isGranted($attribute, $pobox, $user = null)
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
            case self::SHOW:
                // the data object could have for example a method isPrivate()
                // which checks the Boolean attribute $private
                //if (!$user->isPrivate()) {
                //    return true;
                //}
		
		$roles = $user->getRoles();
		if ($pobox->getUser() === $user) {
                    return true;
                }
		
		if ($this->has_role($roles, 'ROLE_ADMIN')) {
		    return true;
		}
		if ($this->has_role($roles, 'ROLE_ADMIN_POBOX')) {
		    return true;
		}
		$countryto = $user->getAgency()->getCity()->getstate()->getCountry();
		$countrypobox = $pobox->getCustomer()->getAdrdefault()->getCity()->getState()->getCountry();
		// exit(\Doctrine\Common\Util\Debug::dump($this->has_role($roles, 'ROLE_VIEW_POBOX'))); 
		if ($this->has_role($roles, 'ROLE_VIEW_POBOX') && ($countryto === $countrypobox)) {
		    return true;
		}
		break;
        }

        return false;
    }
    private function has_role($roles,$rolename) {
	foreach ($roles as $role) {
		if ($role->getName() === $rolename) {
			return true;
		}
	}
	return false;
    }
}
?>
