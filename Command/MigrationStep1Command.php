<?php
// src/Fuel/Bundle/Command/GetUserCommand.php
namespace NvCarga\Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use NvCarga\Bundle\Entity\Baddress;
use NvCarga\Bundle\Entity\Receipt;
use NvCarga\Bundle\Entity\Region;
use NvCarga\Bundle\Entity\City;
use NvCarga\Bundle\Entity\Maincompany;
use NvCarga\Bundle\Entity\Customer;
use NvCarga\Bundle\Entity\Agency;
use NvCarga\Bundle\Entity\Guide;
use NvCarga\Bundle\Entity\Consolidated;
use NvCarga\Bundle\Entity\User;
use NvCarga\Bundle\Entity\Tariff;
use NvCarga\Bundle\Entity\Profile;


class MigrationStep1Command extends ContainerAwareCommand {
    protected function configure() {
        $this
        // the name of the command (the part after "app/console")
        ->setName('app:migration-step1')

        // the short description shown while running "php app/console list"
        ->setDescription('Prueba la conexión y lectura de dos bases de datos')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('Permite probar la conexion y lectura simultanea desde DOS bases de datos diferentes')
        ->addArgument('homepage', InputArgument::REQUIRED, 'The homepage for new system')
    ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('INICIANDO MIGRACION PASO 1..');
        
        $emMULTI = $this->getContainer()->get('doctrine')->getManager();
        $emOLD = $this->getContainer()->get('doctrine')->getManager('migration');
        $main1 = $emMULTI->getRepository('NvCargaBundle:Maincompany')->find(1);        
        $main2 = $emOLD->getRepository('NvCargaBundle:Maincompany')->find(1);
        
        $homepage = $input->getArgument('homepage');
        
        if ($emMULTI->getRepository('NvCargaBundle:Maincompany')->findOneByName($main2->getName())) {
            $output->writeln('Ya existe una empresa con ese NOMBRE');
            return -1;
        }
        if ($emMULTI->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage)) {
            $output->writeln('Ya existe una empresa con ese HOMEPAGE');
            return -1;
        }
        if ($emMULTI->getRepository('NvCargaBundle:Maincompany')->findOneByAcronym($main2->getAcronym())) {
            $output->writeln('Ya existe una empresa con ese ACRONIMO');
            return -1;
        }
        // IMAGEN POR OMISION PARA LA EMPRESA
        $main2->setLogo('default.png'); 
        
        // HOMEPAGE DE LA EMPRESA
        $main2->setHomepage($homepage);
        
        $plan = $emMULTI->getRepository('NvCargaBundle:Plan')->find(3);
        $main2->setPlan($plan);
        $main2->setInactive(false);
        
        // ASIGNACION DE LOS PAISES y REGIONES 
        $allcountries = $emOLD->getRepository('NvCargaBundle:Country')->findAll();
        $count=0;
        $output->writeln('Creando paises y Regiones..');
        foreach ($allcountries as $country) {
            $newcountry = $emMULTI->getRepository('NvCargaBundle:Country')->findOneByName($country->getName());
            $companies = $emOLD->getRepository('NvCargaBundle:Company')->findByCountry($country);
            // COMPAÑIAS POR PAIS
            
            foreach ($companies as $company) {
                $company->setCountry($newcountry);
                $company->setMaincompany($main2);
                $emMULTI->persist($company);
                $count++;
            }

            $localcompanies = $emOLD->getRepository('NvCargaBundle:Localcompany')->findByCountry($country);
            // EMPRESAS DE DISTRIBUCION LOCAL POR PAIS
            foreach ($localcompanies as $localcompany) {
                $localcompany->setCountry($newcountry);
                $localcompany->setMaincompany($main2);
                $emMULTI->persist($localcompany);
            }
            $main2->addCountry($newcountry);
            $regions = $emOLD->getRepository('NvCargaBundle:Region')->findByCountry($country);
            foreach ($regions as $region) {
                $cities = $region->getRegionCities();
                $name = $region->getName();
                $thereg = new Region();
                $thereg->setMaincompany($main2);
                $thereg->setCountry($newcountry);
                $thereg->setName($name);
                $pos1 = strpos(strtolower($name), 'todas');
                $pos2 = strpos(strtolower($name), strtolower($country->getName()));
                if (($pos1 === false) || ($pos2 === false)) {
                    foreach ($cities as $city) {
                        $st = $city->getState();
                        $sag = $emMULTI->getRepository('NvCargaBundle:State')->findOneBy(['name'=>$st->getName(), 'country' => $newcountry]);
                        $cityr = $emMULTI->getRepository('NvCargaBundle:City')->findOneBy(['name'=>$city->getName(), 'state' => $sag]);
                        
                        if (!$cityr) {
                            $cityr = new City();
                            $cityr->setName($city->getName());
                            $cityr->setState($sag);
                            $cityr->setActive(true);
                            $output->writeln('Ciudad ' . $cityr->getName() . ' ' . $cityr->getActive());
                            $emMULTI->persist($cityr);
                        } 
                        $noin = true;
                        foreach ($cityr->getRegions() as $creg) {
                            if ($creg->getName() == $thereg->getName()) {
                                $noin = false;
                                break;
                            }
                        }
                        if ($noin) {
                            $cityr->addRegion($thereg);
                            $thereg->addCity($cityr);
                        }
                        // $cityr->addRegion($thereg);
                        //$output->writeln($cityag->getName());
                        
                        
                    }
                    $emMULTI->persist($thereg);
                }
            }
            $name = 'Todas las ciudades de '. $country->getName();
            $thereg = new Region();
            $thereg->setMaincompany($main2);
            $thereg->setCountry($newcountry);
            $thereg->setName($name);
            $emMULTI->persist($thereg);
        }
        // $output->writeln('Creados paises y Regiones..');    
        $main2->setCountcompanies($count);
        $output->writeln('Creando ETIQUETAS, CONDICIONES, CUENTAS y MEDIDAS... y otras cosas');  
        // FORMATOS DE ETIQUETAS
        $labels = $emOLD->getRepository('NvCargaBundle:Labelconf')->findAll();
        foreach ($labels as $label) {
            $label->setMaincompany($main2);
            $emMULTI->persist($label);
        }
        // TERMINOS y CONDICIONES
        $terms = $emOLD->getRepository('NvCargaBundle:Termcond')->findAll();
        foreach ($terms as $term) {
            $term->setMaincompany($main2);
            $emMULTI->persist($term);
        }
        // CUENTAS BANCARIAS
        $accounts = $emOLD->getRepository('NvCargaBundle:Account')->findAll();
        $count = 0;
        foreach ($accounts as $account) {
            $account->setMaincompany($main2);
            $city = $account->getCity();
            $state = $city->getState();
            $country = $state->getCountry();
            $cag = $emMULTI->getRepository('NvCargaBundle:Country')->findOneByName($country->getName());
            $sag = $emMULTI->getRepository('NvCargaBundle:State')->findOneBy(['name'=>$state->getName(), 'country' => $cag]);
            $cityag = $emMULTI->getRepository('NvCargaBundle:City')->findOneBy(['name'=>$city->getName(), 'state' => $sag]);
            if (!$cityag) {
                $cityag = new City();
                $cityag->setName($city->getName());
                $cityag->setState($sag);
                $cityag->setActive(true);
                $output->writeln('Ciudad ' . $cityag->getName() . ' ' . $cityag->getActive());
                $emMULTI->persist($cityag);
            } 
            $account->setCity($cityag);
            $output->writeln('Ciudad ' . $cityag->getName() . ' ' . $cityag->getActive());
            $emMULTI->persist($account);
            $count++;
        }
        $main2->setCountaccounts($count);
        
        // MEDIDAS
        $measures = $emOLD->getRepository('NvCargaBundle:Measure')->findAll();
        foreach ($measures as $measure) {
            $measure->setMaincompany($main2);
            $emMULTI->persist($measure);
        }
        // OJO FALTA LOS TIPOS DE PAGO y su verificcación
        $ptypes = $emOLD->getRepository('NvCargaBundle:Paidtype')->findAll();
        foreach ($ptypes as $ptype) {
            $paidtype = $emMULTI->getRepository('NvCargaBundle:Paidtype')->findOneByName($ptype->getName());
            
            $ptype->setMaincompany($main2);
            $emMULTI->persist($ptype);
        }
        // OJO FALTA LOS CARRIERS y su verificcación
        $carriers = $emOLD->getRepository('NvCargaBundle:Carrier')->findAll();
        foreach ($carriers as $carrier) {
            $carrier->setMaincompany($main2);
            $emMULTI->persist($carrier);
        }
        $office = $emOLD->getRepository('NvCargaBundle:Office')->find(1);
        $office->setMaincompany($main2);
        $emMULTI->persist($office);
        
        $news = $emOLD->getRepository('NvCargaBundle:News')->findAll();
        foreach ($news as $new) {
            $new->setMaincompany($main2);
            $emMULTI->persist($new);
        }
        
        // SERVICIOS ADICIONALES
        $count=0;
        $adservices = $emOLD->getRepository('NvCargaBundle:Adservice')->findAll();
        foreach ($adservices as $adservice) {
            $adservice->setMaincompany($main2);
            $emMULTI->persist($adservice);
            $count++;
        }
        $main2->setCountadservices($count);
        $emMULTI->persist($main2);
        $emMULTI->flush();
        
        // DEPENDENCIAS EN SERVICIOS ADICIONALES
        $adservices = $emOLD->getRepository('NvCargaBundle:Adservice')->findAll();
        foreach ($adservices as $adservice) {
            $list = $adservice->getDependofMe();
            $theserv = $emMULTI->getRepository('NvCargaBundle:Adservice')->findOneBy(['name'=>$adservice->getName(), 'maincompany'=>$main2]);
            foreach ($list as $serv) {
                $depserv = $emMULTI->getRepository('NvCargaBundle:Adservice')->findOneBy(['name'=>$serv->getName(), 'maincompany'=>$main2]);
                $theserv->addDependofMe($depserv);
            }
        }
        $emMULTI->flush();
        $output->writeln('Finalizado el PASO 1');
        return true;
    }
}
