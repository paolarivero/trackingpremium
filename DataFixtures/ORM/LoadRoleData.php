<?php 
// src/NvCargaBundle/DataFixtures/ORM/LoadRoleData.php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use NvCarga\Bundle\Entity\Role;
use NvCarga\Bundle\Entity\Profile;

class LoadRoleData extends AbstractFixture implements OrderedFixtureInterface
{
	public function load(ObjectManager $manager) 
	{
		$role1 = new Role();
		$role1->setName('ROLE_ADMIN');
		$role1->setDescription('Administrador del Sistema.');
		$role1->setCreationdate(new \DateTime());
		$manager->persist($role1);
		$this->addReference('role1', $role1);
		
		$rolemain = new Role();
		$rolemain->setName('ROLE_ADMIN_MAINCOMPANY');
		$rolemain->setDescription('Administrador de EMPRESAS PRINCIPALES.');
		$rolemain->setCreationdate(new \DateTime());
		$manager->persist($rolemain);
		$this->addReference('$rolemain', $rolemain);

		$role1a = new Role();
		$role1a->setName('ROLE_ADMIN_MULTIAGENCY');
		$role1a->setDescription('Rol que permite crear una guía en cualquier agencia');
		$role1a->setCreationdate(new \DateTime());
		$manager->persist($role1a);
		$this->addReference('role1a', $role1a);

		$role2 = new Role();
		$role2->setName('ROLE_USER');
		$role2->setDescription('Rol de usuario del Sistema.');
		$role2->setCreationdate(new \DateTime());
		$manager->persist($role2);
		$this->addReference('role2', $role2);

		$role = new Role();
		$role->setName('ROLE_ADMIN_AGENCY');
		$role->setDescription('Administrador de Agency');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_AGENCY');
		$role->setDescription('Buscar o listar Agency');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_AGENCYSTATUS');
		$role->setDescription('Administrador de Agencystatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_AGENCYSTATUS');
		$role->setDescription('Buscar o listar Agencystatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_AGENCYTYPE');
		$role->setDescription('Administrador de Agencytype');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_AGENCYTYPE');
		$role->setDescription('Buscar o listar Agencytype');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_BILL');
		$role->setDescription('Administrador de Bill');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_BILL');
		$role->setDescription('Buscar o listar Bill');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_BILLSTATUS');
		$role->setDescription('Administrador de Billstatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_BILLSTATUS');
		$role->setDescription('Buscar o listar Billstatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_CARRIER');
		$role->setDescription('Administrador de Carrier');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_CARRIER');
		$role->setDescription('Buscar o listar Carrier');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_CITY');
		$role->setDescription('Administrador de City');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_CITY');
		$role->setDescription('Buscar o listar City');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_COD');
		$role->setDescription('Administrador de COD');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_COD');
		$role->setDescription('Buscar o listar COD');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_COMPANY');
		$role->setDescription('Administrador de Company');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_COMPANY');
		$role->setDescription('Buscar o listar Company');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_CONSOLIDATED');
		$role->setDescription('Administrador de Consolidated');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_CONSOLIDATED');
		$role->setDescription('Buscar o listar Consolidated');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_CONSOLIDATEDSTATUS');
		$role->setDescription('Administrador de Consolidatedstatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_CONSOLIDATEDSTATUS');
		$role->setDescription('Buscar o listar Consolidatedstatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_COUNTRY');
		$role->setDescription('Administrador de Country');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_COUNTRY');
		$role->setDescription('Buscar o listar Country');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_CUSTOMER');
		$role->setDescription('Administrador de Customer');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_CUSTOMER');
		$role->setDescription('Buscar o listar Customer');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_CUSTOMERSTATUS');
		$role->setDescription('Administrador de Customerstatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_CUSTOMERSTATUS');
		$role->setDescription('Buscar o listar Customerstatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_CUSTOMERTYPE');
		$role->setDescription('Administrador de Customertype');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_CUSTOMERTYPE');
		$role->setDescription('Buscar o listar Customertype');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_GUIDE');
		$role->setDescription('Administrador de Guide');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_GUIDE');
		$role->setDescription('Buscar o listar Guide');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_GUIDESTATUS');
		$role->setDescription('Administrador de Guidestatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_GUIDESTATUS');
		$role->setDescription('Buscar o listar Guidestatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_INSURENCE');
		$role->setDescription('Administrador de Insurance');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_INSURENCE');
		$role->setDescription('Buscar o listar Insurance');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_LOCALCOMPANY');
		$role->setDescription('Administrador de Localcompany');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_LOCALCOMPANY');
		$role->setDescription('Buscar o listar Localcompany');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_LOCATION');
		$role->setDescription('Administrador de Location');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_LOCATION');
		$role->setDescription('Buscar o listar Location');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_MESSAGE');
		$role->setDescription('Administrador de Message');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_MESSAGE');
		$role->setDescription('Buscar o listar Message');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_MOVECONSOLS');
		$role->setDescription('Administrador de Moveconsols');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_MOVECONSOLS');
		$role->setDescription('Buscar o listar Moveconsols');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_MOVEGUIDES');
		$role->setDescription('Administrador de Moveguides');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_MOVEGUIDES');
		$role->setDescription('Buscar o listar Moveguides');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_NEWS');
		$role->setDescription('Administrador de News');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_NEWS');
		$role->setDescription('Buscar o listar News');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_OFFICE');
		$role->setDescription('Administrador de Office');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_OFFICE');
		$role->setDescription('Buscar o listar Office');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_PAIDTYPE');
		$role->setDescription('Administrador de Paidtype');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_PAIDTYPE');
		$role->setDescription('Buscar o listar Paidtype');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_POBOX');
		$role->setDescription('Administrador de Pobox');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_POBOX');
		$role->setDescription('Buscar o listar Pobox');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_POBOXSTATUS');
		$role->setDescription('Administrador de Poboxstatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_POBOXSTATUS');
		$role->setDescription('Buscar o listar Poboxstatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_POBOXTYPE');
		$role->setDescription('Administrador de Poboxtype');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_POBOXTYPE');
		$role->setDescription('Buscar o listar Poboxtype');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_RECEIPT');
		$role->setDescription('Administrador de Receipt');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_RECEIPT');
		$role->setDescription('Buscar o listar Receipt');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_RECEIPTSTATUS');
		$role->setDescription('Administrador de Receiptstatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_RECEIPTSTATUS');
		$role->setDescription('Buscar o listar Receiptstatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_RECEIPTTYPE');
		$role->setDescription('Administrador de Receipttype');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_RECEIPTTYPE');
		$role->setDescription('Buscar o listar Receipttype');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_ROLE');
		$role->setDescription('Administrador de Role');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_ROLE');
		$role->setDescription('Buscar o listar Role');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_SERVICETYPE');
		$role->setDescription('Administrador de Servicetype');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_SERVICETYPE');
		$role->setDescription('Buscar o listar Servicetype');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_SHIPPINGTYPE');
		$role->setDescription('Administrador de Shippingtype');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_SHIPPINGTYPE');
		$role->setDescription('Buscar o listar Shippingtype');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_STATE');
		$role->setDescription('Administrador de State');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_STATE');
		$role->setDescription('Buscar o listar State');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_TARIFF');
		$role->setDescription('Administrador de Tariff');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_TARIFF');
		$role->setDescription('Buscar o listar Tariff');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_TAX');
		$role->setDescription('Administrador de Tax');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_TAX');
		$role->setDescription('Buscar o listar Tax');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_TRANSPORTER');
		$role->setDescription('Administrador de Transporter');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_TRANSPORTER');
		$role->setDescription('Buscar o listar Transporter');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_USER');
		$role->setDescription('Administrador de User');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_USER');
		$role->setDescription('Buscar o listar User');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_USERSTATUS');
		$role->setDescription('Administrador de Userstatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_USERSTATUS');
		$role->setDescription('Buscar o listar Userstatus');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_ADMIN_WAREHOUSE');
		$role->setDescription('Administrador de Warehouse');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role = new Role();
		$role->setName('ROLE_VIEW_WAREHOUSE');
		$role->setDescription('Buscar o listar Warehouse');
		$role->setCreationdate(new \DateTime());
		$manager->persist($role);
		unset($role);
 
		$role3 = new Role();
		$role3->setName('ROLE_CREATE_POBOX');
		$role3->setDescription('Rol para crear POBOX desde área pública');
		$role3->setCreationdate(new \DateTime());
		$manager->persist($role3);
		$this->addReference('role3', $role3);
		
		$manager->flush();
 
	}
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 1;
    }
}
?>
