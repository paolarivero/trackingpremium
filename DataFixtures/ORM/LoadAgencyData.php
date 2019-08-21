<?php 
// src/NvCargaBundle/DataFixtures/ORM/LoadAgencyData.php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use NvCarga\Bundle\Entity\Agency;
use NvCarga\Bundle\Entity\Agencytype;
use NvCarga\Bundle\Entity\Agencystatus;
use NvCarga\Bundle\Entity\Warehouse;
use NvCarga\Bundle\Entity\Maincompany;
use NvCarga\Bundle\Entity\Company;
use NvCarga\Bundle\Entity\Region;
use NvCarga\Bundle\Entity\Format;
use NvCarga\Bundle\Entity\Tariff;
use NvCarga\Bundle\Entity\Servicetype;
use NvCarga\Bundle\Entity\Shippingtype;
use NvCarga\Bundle\Entity\Measure;

class LoadAgencyData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $country1 = $manager->getRepository('NvCargaBundle:Country')->findOneByName('USA');
        $country2 = $manager->getRepository('NvCargaBundle:Country')->findOneByName('VENEZUELA');
        $state = $manager->getRepository('NvCargaBundle:State')->findOneBy(['name'=>'Florida', 'country'=>$country1]);
        // echo $state->getName();
        $city = $manager->getRepository('NvCargaBundle:City')->findOneBy(array('name'=> 'Largo', 'state'=> $state));
        /* $state2 = $manager->getRepository('NvCargaBundle:State')->findOneByName('Mérida');
        echo $state->getName();
        $city2 = $manager->getRepository('NvCargaBundle:City')->findOneBy(array('name'=> 'Mérida', 'state'=> $state2));
        */
        if (!$city) {
            print('No encontré la ciudad');
            return false;
        }
        $countries = $manager->getRepository('NvCargaBundle:Country')->findById([$country1->getId(),$country2->getId()]);
        
        $agencyst = new Agencystatus();
        $agencyst->setName('ACTIVA');
        $agencyst->setDescription('Operativa y recibiendo paquetes');
        $agencyst->setCreationdate(new \DateTime());
        $manager->persist($agencyst);

        $agencyty = new Agencytype();
        $agencyty->setName('MASTER');
        $agencyty->setDescription('Agencia principal de la empresa, puede realizar todas las operaciones');
        $agencyty->setCreationdate(new \DateTime());
        $manager->persist($agencyty);

        $agencyty2 = new Agencytype();
        $agencyty2->setName('RECEPTORA');
        $agencyty2->setDescription('Recibe Paquetes');
        $agencyty2->setCreationdate(new \DateTime());
        $manager->persist($agencyty2);
        /*
        $agencyst = $manager->getRepository('NvCargaBundle:Agencystatus')->findOneByName('ACTIVA');
        $agencyty = $manager->getRepository('NvCargaBundle:Agencytype')->findOneByName('MASTER');
        */
        $text = <<<EOT
        
BIENVENIDO A TRACKINGPREMIUM VERSION MULTIEMPRESA,

YA PUEDES COMENZAR A REALIZAR TUS COMPRAS ON-LINE Y RECIBIRLAS EN LA PUERTA DE TU CASA U OFICINA SIN PROBLEMAS.
A PARTIR DE ESTE MOMENTO YA TIENES UNA DIRECCIÓN PROPIA EN MIAMI.

EOT;
        
        $maincompany1= new Maincompany();
        $maincompany1->setName('Multitrack');
        $maincompany1->setDimfactor(166);
        $maincompany1->setAcronym('MULTI');
        $maincompany1->setPrefixpobox('POBOX-');
        $maincompany1->setPrefixguide('Guide-');
        $maincompany1->setPrefixconsol('CONSOL-');
        $maincompany1->setUrl('trackingpremium.com');
        $maincompany1->setHomepage('multib.trackingpremium.test');
        $maincompany1->setEmail('multitrack@trackingpremium.com');
        $maincompany1->setPoboxmsg($text);
        $maincompany1->setRoundweight('Ninguno');
        $maincompany1->setRoundvol('Ninguno');
        $maincompany1->setRoundtotal(true);
        $maincompany1->setCustomername(true);
        $maincompany1->setCompanyname(false);
        $maincompany1->setNumbername(true);
        $maincompany1->setIninum(1020);
        $maincompany1->setIniguide(1);
        $maincompany1->setConvertvol(1728);
        $maincompany1->setMaxguides(100);
        $maincompany1->setMaxreceipts(100);
        $maincompany1->setMaxconsolidates(100);
        $maincompany1->setMaxbills(100);
        $maincompany1->setMaxcustomers(100);
        $maincompany1->setMaxusers(100);
        $maincompany1->setMaxpoboxes(100);
        $maincompany1->setMaxagencies(100);
        $maincompany1->setMaxbags(100);
        $maincompany1->setMaxaccounts(100);
        $maincompany1->setMaxalerts(100);
        $maincompany1->setMaxadservices(100);
        $maincompany1->setMaxcompanies(100);
        $maincompany1->setCountguides(0);
        $maincompany1->setCountreceipts(0);
        $maincompany1->setCountbills(0);
        $maincompany1->setCountbags(0);
        $maincompany1->setCountpoboxes(0);
        $maincompany1->setCountcustomers(0);
        $logo = 'logo.png';
        $filename = md5(uniqid()) . '.png';
        $maincompany1->setLogo($filename);
        copy(dirname(__FILE__) .'/' . $logo, '/home/hidrobo/PERSONAL/PROYECTOS/NVCARGA/TEST/multidemo/web/logos/'. $filename);
        // $this->addReference('maincompany', $maincompany1);
        $maincompany1->addCountry($country1);
        $maincompany1->addCountry($country2);
        $country1->addMaincompany($maincompany1);
        $country2->addMaincompany($maincompany1);
        $manager->persist($maincompany1);

        $warehouse1 = new Warehouse();
        $warehouse1->setName('MULTITRACK Warehouse1');
        $warehouse1->setAddress('10475 Whittigton Ct');
        $warehouse1->setDescription('Bodega Principal de Paquetes');
        $warehouse1->setZip('33773');
        $warehouse1->setCreationdate(new \DateTime());
        $warehouse1->setLastupdate(new \DateTime());
        $warehouse1->setCity($city);
        $warehouse1->setMaincompany($maincompany1);
        $manager->persist($warehouse1);

        $agency1 = new Agency();
        $agency1->setName('MULTITRACK Largo ');
        $agency1->setAddress('10475 Whittigton Ct');
        $agency1->setZip('33773');
        $agency1->setCreationdate(new \DateTime());
        $agency1->setLastupdate(new \DateTime());
        $agency1->setWarehouse($warehouse1);
        $agency1->setCity($city);
        $agency1->setPhone('000-000-0000');
        $agency1->setFax('');
        $agency1->setEmail('multitrack@trackingpremium.com');
        $agency1->setManager('Paola Rivero');
        $agency1->setWebmaster('Paola Rivero');
        $agency1->setGuidecopies(FALSE);
        $agency1->setPoboxs(TRUE);
        $agency1->setStatus($agencyst);
        $agency1->setType($agencyty);
        $agency1->setMaincompany($maincompany1);
        $maincompany1->addAgency($agency1);
        $this->addReference('agency1', $agency1);
        $manager->persist($agency1);
        $format = new Format();
        $format->setMaincompany($maincompany1);
        $maincompany1->setFormat($format);
        $manager->persist($format);
        
        // AGREGAR DOS TARIFAS BASICAS
       
        $lb = new Measure();
        $lb->setName('Lb');
        $lb->setLabel('Lb');
        $lb->setMaincompany($maincompany1);
        $lb->setDescription('Medida en Kilogramos');
        $manager->persist($lb);

        $cf = new Measure();
        $cf->setName('CF');
        $cf->setLabel('CF');
        $cf->setMaincompany($maincompany1);
        $cf->setDescription('Medida en Metros Cúbicos');
        $manager->persist($cf);
        
        $servAereo = new Shippingtype();
        $servAereo->setName('Aéreo');
        $servAereo->setDescription('Transporte Aéreo');
        $servAereo->setCreationdate(new \DateTime());
        $manager->persist($servAereo);

        $servMar = new Shippingtype();
        $servMar->setName('Marítimo');
        $servMar->setDescription('Transporte Marítimo');
        $servMar->setCreationdate(new \DateTime());
        $manager->persist($servMar);

        $serviciot1 = new Servicetype();
        $serviciot1->setName('Aéreo A1');
        $serviciot1->setShippingtype($servAereo);
        $serviciot1->setAgency($agency1);
        $serviciot1->setCreationdate(new \DateTime());
        $manager->persist($serviciot1);

        $serviciot2 = new Servicetype();
        $serviciot2->setName('Marítimo A1');
        $serviciot2->setShippingtype($servMar);
        $serviciot2->setAgency($agency1);
        $serviciot2->setCreationdate(new \DateTime());
        $manager->persist($serviciot2);
        
        foreach ($countries as $country) {
            $company = new Company();
            $company->setName('Multitrack en ' . $country->getName());
            $company->setCreationdate(new \DateTime());
            $company->setComment('Empresa Principal en ' . $country->getName() );
            $company->setCountry($country);
            $company->setMaincompany($maincompany1);
            $manager->persist($company);
            // if ($country->getName() != 'USA') {
                $states = $manager->getRepository('NvCargaBundle:State')->findByCountry($country);
                $region = new Region();
                $region->setName('Todas las ciudades de '. $country->getName());
                $region->setCountry($country);
                $region->setMaincompany($maincompany1);
                /*
                foreach ($states as $state) {
                    $cities = $manager->getRepository('NvCargaBundle:City')->findByState($state);
                    foreach ($cities as $city) {
                        $region->addCity($city);
                    }
                }
                */
                // AGREGAR DOS TARIFAS BASICAS
                
                $name = 'Aérea General ' . $country->getName();
                $tariff1 = new Tariff();
                $tariff1->setAgency($agency1);
                $tariff1->setRegion($region); 
                $tariff1->setLastupdate(new \DateTime());
                $tariff1->setShippingtype($servAereo);
                $tariff1->setName('Tarifa ' . $name);
                $tariff1->setMeasure($lb);
                $tariff1->setInsurance(false);
                $tariff1->setInsurancePer(0);
                $tariff1->setTax(false);
                $tariff1->setTaxPer(0);
                $tariff1->setDimentional(false);
                $tariff1->setCost(0);
                $tariff1->setBegin(0.01);
                $tariff1->setUntil(5000);
                $tariff1->setMinimun(0.01);
                $tariff1->setValueMeasure(1);
                $tariff1->setValueMin(1);
                $tariff1->setMinimunLimit('Total');
                $tariff1->setProfitAg(0);
                $tariff1->setVolumePrice(0);
                $tariff1->setProfitAgv(0);
                $tariff1->setAdditional(0);
                $tariff1->setLabelAdditional('');
                $tariff1->setActive(true);
                $manager->persist($tariff1);
                
                $name = 'Marítima General ' . $country->getName();
                $tariff2 = new Tariff();
                $tariff2->setAgency($agency1);
                $tariff2->setRegion($region); 
                $tariff2->setLastupdate(new \DateTime());
                $tariff2->setShippingtype($servMar);
                $tariff2->setName('Tarifa ' . $name);
                $tariff2->setMeasure($cf);
                $tariff2->setInsurance(false);
                $tariff2->setInsurancePer(0);
                $tariff2->setTax(false);
                $tariff2->setTaxPer(0);
                $tariff2->setDimentional(false);
                $tariff2->setCost(0);
                $tariff2->setBegin(0.01);
                $tariff2->setUntil(5000);
                $tariff2->setMinimun(0.01);
                $tariff2->setValueMeasure(1);
                $tariff2->setValueMin(1);
                $tariff2->setMinimunLimit('Total');
                $tariff2->setProfitAg(0);
                $tariff2->setVolumePrice(0);
                $tariff2->setProfitAgv(0);
                $tariff2->setAdditional(0);
                $tariff2->setLabelAdditional('');
                $tariff2->setActive(true);
                $manager->persist($tariff2);
                
                $manager->persist($region);
            // }
        }
        
        $manager->flush();
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 2;
    }
}
?>
