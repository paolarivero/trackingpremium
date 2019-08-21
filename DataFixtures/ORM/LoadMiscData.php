<?php 
// src/NvCargaBundle/DataFixtures/ORM/LoadMiscData.php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use NvCarga\Bundle\Entity\Company;
use NvCarga\Bundle\Entity\COD;
use NvCarga\Bundle\Entity\Carrier;
use NvCarga\Bundle\Entity\Customertype;
use NvCarga\Bundle\Entity\Customerstatus;

use NvCarga\Bundle\Entity\Office;
use NvCarga\Bundle\Entity\Receipttype;
use NvCarga\Bundle\Entity\Receiptstatus;
use NvCarga\Bundle\Entity\Poboxtype;
use NvCarga\Bundle\Entity\Poboxstatus;
use NvCarga\Bundle\Entity\Billstatus;
use NvCarga\Bundle\Entity\Localcompany;
use NvCarga\Bundle\Entity\Paidtype;
use NvCarga\Bundle\Entity\Maincompany;


use NvCarga\Bundle\Entity\Tariff;
use NvCarga\Bundle\Entity\Guidestatus;
use NvCarga\Bundle\Entity\Consolidatedstatus;
use NvCarga\Bundle\Entity\Region;
use NvCarga\Bundle\Entity\Termcond;
use NvCarga\Bundle\Entity\Labelconf;


class LoadMiscData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $country1 = $manager->getRepository('NvCargaBundle:Country')->findOneByName('USA');
        $country2 = $manager->getRepository('NvCargaBundle:Country')->findOneByName('VENEZUELA');
        if (!$country1) {
            print('No encontré el país');
            return false;
        }

        $cod1 = new COD();
        $cod1->setName('C.O.D');
        $cod1->setCreationdate(new \DateTime());
        $cod1->setDescription('Cobro en destino');
        $manager->persist($cod1);

        $cod2 = new COD();
        $cod2->setName('Prepagado');
        $cod2->setCreationdate(new \DateTime());
        $cod2->setDescription('Cobro tipo Prepagado');
        $manager->persist($cod2);

        $rectype1 = new Receipttype();
        $rectype1->setName('Casillero');
        $rectype1->setCreationdate(new \DateTime());
        $rectype1->setDescription('Recibo para paquetes a casilleros');
        $manager->persist($rectype1);

        $rectype2 = new Receipttype();
        $rectype2->setName('Pickup');
        $rectype2->setCreationdate(new \DateTime());
        $rectype2->setDescription('Recibo para paquetes Pickup');
        $manager->persist($rectype2);

        $rectype3 = new Receipttype();
        $rectype3->setName('Clientes');
        $rectype3->setCreationdate(new \DateTime());
        $rectype3->setDescription('Recibo para paquetes entregados por clientes');
        $manager->persist($rectype3);

        $rectype4 = new Receipttype();
        $rectype4->setName('Master');
        $rectype4->setCreationdate(new \DateTime());
        $rectype4->setDescription('Recibo especial para reempaque de paquetes');
        $manager->persist($rectype4);
        
        $rectype5 = new Receipttype();
        $rectype5->setName('Reempaque');
        $rectype5->setCreationdate(new \DateTime());
        $rectype5->setDescription('Recibo especial para reempaque de otros Recibos');
        $manager->persist($rectype5);

        $recstatus1 = new Receiptstatus();
        $recstatus1->setName('Por procesar');
        $recstatus1->setCreationdate(new \DateTime());
        $recstatus1->setDescription('Recibo que aún no tiene guía');
        $manager->persist($recstatus1);

        $recstatus2 = new Receiptstatus();
        $recstatus2->setName('Procesado');
        $recstatus2->setCreationdate(new \DateTime());
        $recstatus2->setDescription('Se creo la guía para el recibo');
        $manager->persist($recstatus2);

        $recstatus3 = new Receiptstatus();
        $recstatus3->setName('Anulado');
        $recstatus3->setCreationdate(new \DateTime());
        $recstatus3->setDescription('El recibo ha sido anulado');
        $manager->persist($recstatus3);
        
        $recstatus4 = new Receiptstatus();
        $recstatus4->setName('Reempacado');
        $recstatus4->setCreationdate(new \DateTime());
        $recstatus4->setDescription('El recibo ha sido reempacado en otro recibo');
        $manager->persist($recstatus4);

        $postatus1 = new Poboxstatus();
        $postatus1->setName('ACTIVO');
        $postatus1->setCreationdate(new \DateTime());
        $postatus1->setDescription('Recibiendo envíos');
        $manager->persist($postatus1);

        $postatus2 = new Poboxstatus();
        $postatus2->setName('SUSPENDIDO');
        $postatus2->setCreationdate(new \DateTime());
        $postatus2->setDescription('Temporalmente suspendido');
        $manager->persist($postatus2);

        $potype1 = new Poboxtype();
        $potype1->setName('NORMAL');
        $potype1->setCreationdate(new \DateTime());
        $potype1->setDescription('Casillero creado por un empleado');
        $manager->persist($potype1);

        $potype2 = new Poboxtype();
        $potype2->setName('PUBLICO');
        $potype2->setCreationdate(new \DateTime());
        $potype2->setDescription('Casillero creado por el cliente');
        $manager->persist($potype2);


        $guidestatus1 = new Guidestatus();
        $guidestatus1->setName('Recibida');
        $guidestatus1->setPosition(1);
        $guidestatus1->setCreationdate(new \DateTime());
        $guidestatus1->setCountry($country1);
        $guidestatus1->setAddress('Guía Recibida en Agencia');
        $guidestatus1->setIsinherited(false);
        $manager->persist($guidestatus1);

        $guidestatus3 = new Guidestatus();
        $guidestatus3->setPosition(2);
        $guidestatus3->setName('Consolidada');
        $guidestatus3->setCreationdate(new \DateTime());
        $guidestatus3->setCountry($country1);
        $guidestatus3->setAddress('Consolidada en la agencia master');
        $guidestatus3->setIsinherited(false);
        $manager->persist($guidestatus3);

        $guidestatus4 = new Guidestatus();
        $guidestatus4->setName('En tránsito');
        $guidestatus4->setPosition(3);
        $guidestatus4->setCreationdate(new \DateTime());
        $guidestatus4->setCountry($country1);
        $guidestatus4->setAddress('Entregado a la empresa transportista');
        $guidestatus4->setIsinherited(true);
        $manager->persist($guidestatus4);

        $guidestatus5 = new Guidestatus();
        $guidestatus5->setName('En aduana');
        $guidestatus5->setPosition(4);
        $guidestatus5->setCreationdate(new \DateTime());
        $guidestatus5->setCountry($country2);
        $guidestatus5->setAddress('En aduana de país destino');
        $guidestatus5->setIsinherited(true);
        $manager->persist($guidestatus5);

        $guidestatus6 = new Guidestatus();
        $guidestatus6->setName('En bodega destino');
        $guidestatus6->setPosition(5);
        $guidestatus6->setCreationdate(new \DateTime());
        $guidestatus6->setCountry($country2);
        $guidestatus6->setAddress('Entregado a la agencia del país destino');
        $guidestatus6->setIsinherited(true);
        $manager->persist($guidestatus6);

        $guidestatus8 = new Guidestatus();
        $guidestatus8->setName('Coordinada');
        $guidestatus8->setPosition(6);
        $guidestatus8->setCreationdate(new \DateTime());
        $guidestatus8->setCountry($country2);
        $guidestatus8->setAddress('Se entregó al transportista local');
        $guidestatus8->setIsinherited(false);
        $manager->persist($guidestatus8);

        $guidestatus9 = new Guidestatus();
        $guidestatus9->setName('Entregada');
        $guidestatus9->setPosition(7);
        $guidestatus9->setCreationdate(new \DateTime());
        $guidestatus9->setCountry($country2);
        $guidestatus9->setAddress('Entragada al cliente');
        $guidestatus9->setIsinherited(false);
        $manager->persist($guidestatus9);

        $consulstatus1 = new Consolidatedstatus();
        $consulstatus1->setName('Creado');
        $consulstatus1->setPosition(1);
        $consulstatus1->setCreationdate(new \DateTime());
        $consulstatus1->setCountry($country1);
        $consulstatus1->setAddress('Creado en un agencia');
        $consulstatus1->setInherited(false);
        $manager->persist($consulstatus1);

        $consulstatus2 = new Consolidatedstatus();
        $consulstatus2->setName('En tránsito');
        $consulstatus2->setPosition(2);
        $consulstatus2->setCreationdate(new \DateTime());
        $consulstatus2->setCountry($country1);
        $consulstatus2->setAddress('Entregado a la empresa transportista');
        $consulstatus2->setInherited(true);
        $manager->persist($consulstatus2);

        $consulstatus3 = new Consolidatedstatus();
        $consulstatus3->setName('En aduana');
        $consulstatus3->setPosition(3);
        $consulstatus3->setCreationdate(new \DateTime());
        $consulstatus3->setCountry($country2);
        $consulstatus3->setAddress('En aduana de país destino');
        $consulstatus3->setInherited(true);
        $manager->persist($consulstatus3);

        $consulstatus4 = new Consolidatedstatus();
        $consulstatus4->setName('En bodega destino');
        $consulstatus4->setPosition(4);
        $consulstatus4->setCreationdate(new \DateTime());
        $consulstatus4->setCountry($country2);
        $consulstatus4->setAddress('Entregado a la agencia del país destino');
        $consulstatus4->setInherited(true);
        $manager->persist($consulstatus4);

        $consulstatus5 = new Consolidatedstatus();
        $consulstatus5->setName('Procesado en destino');
        $consulstatus5->setPosition(5);
        $consulstatus5->setCreationdate(new \DateTime());
        $consulstatus5->setCountry($country2);
        $consulstatus5->setAddress('Las guías serán despachadas');
        $consulstatus5->setInherited(false);
        $manager->persist($consulstatus5);	

        $maincompanies = $manager->getRepository('NvCargaBundle:Maincompany')->findAll();
        foreach ($maincompanies as $maincompany) {

            $patype1 = new Paidtype();
            $patype1->setName('TDC');
            $patype1->setCreationdate(new \DateTime());
            $patype1->setDescription('Pago con tarjeta de crédito');
            $patype1->setMaincompany($maincompany);
            $manager->persist($patype1);

            $patype2 = new Paidtype();
            $patype2->setName('Débito');
            $patype2->setCreationdate(new \DateTime());
            $patype2->setDescription('Pago con tarjeta de débito');
            $patype2->setMaincompany($maincompany);
            $manager->persist($patype2);

            $patype3 = new Paidtype();
            $patype3->setName('Efectivo');
            $patype3->setCreationdate(new \DateTime());
            $patype3->setDescription('Pago en efectivo');
            $patype3->setMaincompany($maincompany);
            $manager->persist($patype3);
            
            $patype4 = new Paidtype();
            $patype4->setName('Cheque');
            $patype4->setCreationdate(new \DateTime());
            $patype4->setDescription('Pago con cheque');
            $patype4->setMaincompany($maincompany);
            $manager->persist($patype4);

            $patype7 = new Paidtype();
            $patype7->setName('Transferencia ');
            $patype7->setCreationdate(new \DateTime());
            $patype7->setDescription('Pago por transferencia');
            $patype7->setMaincompany($maincompany);
            $manager->persist($patype7);

            
            $labelconf = new Labelconf();
            $labelconf->setLastupdate(new \DateTime());
            $labelconf->setTableclass('Guide');
            $labelconf->setWidth(152);
            $labelconf->setHeight(102);
            $labelconf->setMaincompany($maincompany);
            $manager->persist($labelconf);

        
            $carrier1 = new Carrier();
            $carrier1->setName('Amazon');
            $carrier1->setCreationdate(new \DateTime());
            $carrier1->setDescription('Carrier Amazon');
            $carrier1->setMaincompany($maincompany);
            $manager->persist($carrier1);

            $carrier2 = new Carrier();
            $carrier2->setName('Walmart');
            $carrier2->setCreationdate(new \DateTime());
            $carrier2->setDescription('Carrier Walmart');
            $carrier2->setMaincompany($maincompany);
            $manager->persist($carrier2);

            $carrier3 = new Carrier();
            $carrier3->setName('Currier');
            $carrier3->setCreationdate(new \DateTime());
            $carrier3->setDescription('Paquete entregado por el cliente');
            $carrier3->setMaincompany($maincompany);
            $manager->persist($carrier3);
            
            $carrier0 = new Carrier();
            $carrier0->setName('No registrado');
            $carrier0->setCreationdate(new \DateTime());
            $carrier0->setDescription('El carrier no está registrado en el sistema');
            $carrier0->setMaincompany($maincompany);
            $manager->persist($carrier0);
            
            $carrier4 = new Carrier();
            $carrier4->setName('Reempaque en empresa');
            $carrier4->setCreationdate(new \DateTime());
            $carrier4->setDescription('El paquete ha sido reempacado en la empresa');
            $carrier4->setMaincompany($maincompany);
            $manager->persist($carrier4);
            
            $lcompany1 = new Localcompany();
            $lcompany1->setName('MRW Venezuela');
            $lcompany1->setCreationdate(new \DateTime());
            $lcompany1->setUrl('http://www.mrw.com.ve/');
            $lcompany1->setCountry($country2);
            $lcompany1->setMaincompany($maincompany);
            $manager->persist($lcompany1);

            $lcompany2 = new Localcompany();
            $lcompany2->setName('DHL');
            $lcompany2->setCreationdate(new \DateTime());
            $lcompany2->setUrl('http://www.dhl.com.ve');
            $lcompany2->setCountry($country2);
            $lcompany2->setMaincompany($maincompany);
            $manager->persist($lcompany2);

            $lcompany3 = new Localcompany();
            $lcompany3->setName('Empresa');
            $lcompany3->setCreationdate(new \DateTime());
            $lcompany3->setUrl('http://www.newdemo.com');
            $lcompany3->setCountry($country1);
            $lcompany3->setMaincompany($maincompany);
            $manager->persist($lcompany3);
            
            $oficina = new Office();
            $oficina->setName('Master');
            $oficina->setComment('Oficina principal');
            $oficina->setCreationdate(new \DateTime());
            $oficina->setMaincompany($maincompany);
            $manager->persist($oficina);
        }
        
        $manager->flush();

    }
 
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 3;
    }
}
?>
