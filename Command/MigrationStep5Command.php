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
use NvCarga\Bundle\Entity\Maincompany;
use NvCarga\Bundle\Entity\Customer;
use NvCarga\Bundle\Entity\Agency;
use NvCarga\Bundle\Entity\Guide;
use NvCarga\Bundle\Entity\Consolidated;
use NvCarga\Bundle\Entity\User;
use NvCarga\Bundle\Entity\Tariff;


class MigrationStep5Command extends ContainerAwareCommand {
    protected function configure() {
        $this
        // the name of the command (the part after "app/console")
        ->setName('app:migration-step5')

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
        $output->writeln('INICIANDO MIGRACION PASO 5 ..');

        $emOLD = $this->getContainer()->get('doctrine')->getManager('migration');
        $emMULTI = $this->getContainer()->get('doctrine')->getManager();
        
        $homepage = $input->getArgument('homepage');
        
    
        $main2 = $emMULTI->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        $office = $emMULTI->getRepository('NvCargaBundle:Office')->findOneBy(['maincompany'=>$main2]);
        $type = $emMULTI->getRepository('NvCargaBundle:Agencytype')->findByName('MASTER');
        $themaster = $emMULTI->getRepository('NvCargaBundle:Agency')->findByType($type);
        
        //$output->writeln('BD actualizada... ');
        
        // RECORRER NUEVAMENTE LAS AGENCIAS PARA CREAR LOS CONSOLIDADOS
        $output->writeln('Creando cosolidados... ');
        $agencies = $emOLD->getRepository('NvCargaBundle:Agency')->findAll();
        $count=0;
        foreach ($agencies as $agency) {
            $theagency = $emMULTI->getRepository('NvCargaBundle:Agency')->findOneBy(['name'=>$agency->getName(), 'maincompany'=>$main2]);
            $consols = $emOLD->getRepository('NvCargaBundle:Consolidated')->findByAgency($agency);
            foreach ($consols as $oconsol) {
                $consol = new Consolidated();
                $countryfrom = $emMULTI->getRepository('NvCargaBundle:Country')->findOneBy(['name' => $oconsol->getCountryfrom()->getName()]);
                $countryto = $emMULTI->getRepository('NvCargaBundle:Country')->findOneBy(['name' => $oconsol->getCountryto()->getName()]);
                $from = $emMULTI->getRepository('NvCargaBundle:Company')->findOneBy(['name' => $oconsol->getSender()->getName(), 'maincompany'=>$main2, 'country' => $countryfrom]);
                $to = $emMULTI->getRepository('NvCargaBundle:Company')->findOneBy(['name' => $oconsol->getReceiver()->getName(), 'maincompany'=>$main2, 'country' => $countryto]);
                $servicio = $emMULTI->getRepository('NvCargaBundle:Shippingtype')->findOneBy(['name'=>$oconsol->getShippingtype()->getName()]);
                $consol->setShippingtype($servicio);
                $consol->setCountryfrom($countryfrom);
                $consol->setCountryto($countryto);
                $consol->setSender($from);
                $consol->setReceiver($to);
                $consol->setOffice($office);
                $consol->setAgency($theagency);
                $consol->setMaincompany($main2);
                $consol->setNumber($oconsol->getNumber());
                $consol->setIsopen($oconsol->getIsopen());
                foreach ($oconsol->getGuides() as $guide) {
                    $thegui = $emMULTI->getRepository('NvCargaBundle:Guide')->findOneBy(['number' => $guide->getNumber(), 'maincompany'=>$main2]);
                    $thegui->setConsol($consol);
                    $consol->addGuide($thegui);
                }
                $emMULTI->persist($consol);
                $count++;
                if ($count % 100 == 0) {
                    $emMULTI->flush();
                }
            }
        }
        $main2->setCountconsolidates($count);
        $output->writeln('Consolidados Creados... ');
        
        $emMULTI->flush();
        
        
        // AGREGAR LAS FACTURAS, DEBEN ESTAR CREADAS LAS GUÍAS
        $output->writeln('Creando facturas... ');
        $bills = $emOLD->getRepository('NvCargaBundle:Bill')->findAll();
        $count = 0;
        foreach ($bills as $bill) {
            $bill->setExpdate($bill->getCreationdate());
            $cus = $this->getCustomer($bill->getCustomer(), $main2);
            $bill->setCustomer($cus);
            $cod = $emMULTI->getRepository('NvCargaBundle:COD')->findOneByName($bill->getCOD()->getName());
            $bill->setCOD($cod);
            $paidtype = $emMULTI->getRepository('NvCargaBundle:Paidtype')->findOneBy(['name'=>$bill->getPaidtype()->getName(), 'maincompany'=>$main2]);
            $bill->setPaidtype($paidtype);
            $bill->setMaincompany($main2);
            // $bill->setStatus('COBRADA');
            $total = 0;
            foreach ($bill->getGuides() as $guide) {
                $thegui = $emMULTI->getRepository('NvCargaBundle:Guide')->findOneBy(['number' => $guide->getNumber(), 'maincompany'=>$main2]);
                //$output->writeln('GUIA: '. $guide->getNumber());
                $thegui->setBill($bill);
                $bill->removeGuide($guide);
                if ($bill->getCanceled()) {
                    $bill->setStatus('ANULADA');
                } else {
                    $bill->addGuide($thegui);
                }
                $total+=$thegui->getTotalpaid();
            }
            $bill->setTotal($total);
            $emMULTI->persist($bill);
            if ($bill->getStatus() != 'ANULADA') {
                $count++;
            }
        }
        $main2->setCountbills($count);
        $output->writeln('Facturas Creadas... ');
        // AGREGAR LOS PAGOS, DEBEN ESTAR CREADAS LAS GUÍAS, LAS CUENTAS y LOS CLIENTES
        $pays = $emOLD->getRepository('NvCargaBundle:Payment')->findAll();
        $output->writeln('Creando PAGOS... ');
        foreach ($pays as $pay) {
            $cus = $this->getCustomer($pay->getCustomer(), $main2);
            $pay->setCustomer($cus);
            $thegui = $emMULTI->getRepository('NvCargaBundle:Guide')->findOneBy(['number' => $pay->getGuide()->getNumber(), 'maincompany'=>$main2]);
            $pay->setGuide($thegui);
            $account = $emMULTI->getRepository('NvCargaBundle:Account')->findOneBy(['number' => $pay->getAccount()->getNumber(), 'maincompany'=>$main2]);
            $pay->setAccount($account);
            $emMULTI->persist($pay);
        }
        
        $emMULTI->flush();
        $output->writeln('PAGOS Creados... ');
        
        // RECORRER NUEVAMENTE LAS AGENCIAS PARA CREAR LAS BOLSAS
        $agencies = $emOLD->getRepository('NvCargaBundle:Agency')->findAll();
        $output->writeln('CREANDO BOLSAS... ');
        $count=0;
        foreach ($agencies as $agency) {
            $theagency = $emMULTI->getRepository('NvCargaBundle:Agency')->findOneBy(['name'=>$agency->getName(), 'maincompany'=>$main2]);
            // AGREGAR LAS BOLSAS, DEBEN ESTAR CREADAS LAS GUÍAS
            $bags = $emOLD->getRepository('NvCargaBundle:Bag')->findByAgency($agency);
            foreach ($bags as $bag) {
                $bag->setAgency($theagency);
                $bag->setMaincompany($main2);
                $emMULTI->persist($bag);
                $count++;
                foreach ($bag->getGuides() as $guide) {
                    $thegui = $emMULTI->getRepository('NvCargaBundle:Guide')->findOneBy(['number' => $guide->getNumber(), 'maincompany'=>$main2]);
                    $thegui->setBag($bag);
                    $bag->removeGuide($guide);
                    $bag->addGuide($thegui);
                }
            }
        }
        $main2->setCountbags($count);
        
        $emMULTI->flush();
        $output->writeln('BOLSAS Creadas... ');
        
        
        $output->writeln('Creando SERVICIOS a GUIAS... ');
        // AGREGAR LOS SERVICIOS ADICIONALES A LAS GUIAS, DEBEN ESTAR CREADAS LAS GUÍAS Y LOS SERVICIOS
        $servgs = $emOLD->getRepository('NvCargaBundle:Servtoguide')->findAll();
        foreach ($servgs as $serv) {
            //$output->writeln('Servicio a Guía Número: ' . $serv->getGuide()->getNumber());
            if ($serv->getGuide()->getNumber()) {
                $thegui = $emMULTI->getRepository('NvCargaBundle:Guide')->findOneBy(['number' => $serv->getGuide()->getNumber(), 'maincompany'=>$main2]);
                $serv->setGuide($thegui);
                $adserv = $emMULTI->getRepository('NvCargaBundle:Adservice')->findOneBy(['name' => $serv->getAdservice()->getName(), 'maincompany'=>$main2]);
                $serv->setAdservice($adserv);
                $emMULTI->persist($serv);
            }
        }
        $emMULTI->flush();
        $output->writeln('SERVICIOS DE GUIAS Creados... ');
        
        // AGREGAR LOS MOVIMIENTOS DE LOS CONSOLIDADOS
        $output->writeln('MOVIMIENTOS de  CONSOLIDADOS... ');
        $movecs = $emOLD->getRepository('NvCargaBundle:Moveconsols')->findAll();
        $count = 0;
        foreach ($movecs as $movec) {
            $thecon = $emMULTI->getRepository('NvCargaBundle:Consolidated')->findOneBy(['number' => $movec->getConsolidated()->getNumber(), 'maincompany'=>$main2]);
            $movec->setConsolidated($thecon);
            $thest = $emMULTI->getRepository('NvCargaBundle:Consolidatedstatus')->findOneBy(['name' => $movec->getStatus()->getName()]);
            $movec->setStatus($thest);
            $emMULTI->persist($movec);
            $count++;
            if ($count % 100 == 0) {
                $emMULTI->flush();
            }
        }
        $output->writeln('Movimientos de consolidados  Creados... ');
        $emMULTI->flush();
        
        $lb = $emMULTI->getRepository('NvCargaBundle:Measure')->findOneBy(['name'=>'Lb', 'maincompany'=>$main2]);
        $cf = $emMULTI->getRepository('NvCargaBundle:Measure')->findOneBy(['name'=>'CF', 'maincompany'=>$main2]);
        $servAereo = $emMULTI->getRepository('NvCargaBundle:Shippingtype')->findOneByName('Aéreo');
        $servMar = $emMULTI->getRepository('NvCargaBundle:Shippingtype')->findOneByName('Marítimo');
        
        $agencies = $emMULTI->getRepository('NvCargaBundle:Agency')->findByMaincompany($main2);
        foreach ($agencies as $agency) {
            $countries = $main2->getCountries()->toArray();
            foreach ($countries as $country) {
                $nameregion = 'Todas las ciudades de ' . $country->getName();
                $region = $emMULTI->getRepository('NvCargaBundle:Region')->findOneBy(['maincompany'=>$main2, 'name'=> $nameregion, 'country'=>$country]);
                
                if ($region) {
                    $name = 'Tarifa Aérea General ' . $country->getName();
                    $tariff = $emMULTI->getRepository('NvCargaBundle:Tariff')->findOneBy(['region'=> $region, 'agency'=>$agency, 'name'=>$name]);
                    $output->writeln($region->getName());
                    if (!$tariff) {
                        $tariff1 = new Tariff();
                        $tariff1->setAgency($agency);
                        $tariff1->setRegion($region); 
                        $tariff1->setLastupdate(new \DateTime());
                        $tariff1->setShippingtype($servAereo);
                        $tariff1->setName($name);
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
                        $emMULTI->persist($tariff1);
                        $output->writeln('NUEVA TARIFA: ' . $tariff1->getName());
                    }
                    $name = 'Tarifa Marítima General ' . $country->getName();
                    $tariff = $emMULTI->getRepository('NvCargaBundle:Tariff')->findOneBy(['region'=> $region, 'agency'=>$agency, 'name'=>$name]);
                    if (!$tariff) {
                        $tariff2 = new Tariff();
                        $tariff2->setAgency($agency);
                        $tariff2->setRegion($region); 
                        $tariff2->setLastupdate(new \DateTime());
                        $tariff2->setShippingtype($servMar);
                        $tariff2->setName($name);
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
                        $output->writeln('NUEVA TARIFA: ' . $tariff2->getName());
                        $emMULTI->persist($tariff2);
                    }
                }
            }
        }
        
        $emMULTI->flush();
        $output->writeln('Finalizado el PASO 5');
        return true;
        
    }
    public function getDir(Baddress $dir, Maincompany $main)  {
        $baddress = null;
        if ($dir) {
            $emMULTI = $this->getContainer()->get('doctrine')->getManager();
            $customer = $dir->getCustomer();
            $thecustomer = $emMULTI->getRepository('NvCargaBundle:Customer')
                    ->findBy(['name'=>$customer->getName(), 
                            'lastname'=>$customer->getLastname(), 
                            'email'=> $customer->getEmail(),
                            'creationdate'=>$customer->getCreationdate(),
                            'maincompany'=>$main]);
                            
            $baddress = $emMULTI->getRepository('NvCargaBundle:Baddress')
                ->findOneBy(['name'=>$dir->getName(), 
                            'lastname'=>$dir->getLastname(), 
                            'address'=>$dir->getAddress(),
                            'phone'=>$dir->getPhone(),
                            'mobile'=>$dir->getMobile(),
                            'zip'=>$dir->getZip(),
                            'docid'=>$dir->getDocid(), 
                            'customer'=>$thecustomer]);
        }
        return $baddress;
        
    }
    public function getCustomer(Customer $cus, Maincompany $main)  {
        $customer = null;
        if ($cus) {
            $emMULTI = $this->getContainer()->get('doctrine')->getManager();
            $customer = $emMULTI->getRepository('NvCargaBundle:Customer')
                ->findOneBy(['name'=>$cus->getName(), 
                            'lastname'=>$cus->getLastname(), 
                            'email'=>$cus->getEmail(),
                            'maincompany'=>$main]);
        }
        if (!$customer) {
            print_r('EMAIL: ' . $cus->getEmail(). PHP_EOL);
        }
        return $customer;
    }
    public function getReceipt(Receipt $rec, Maincompany $main)  {
        $receipt = null;
        if ($rec) {
            $emMULTI = $this->getContainer()->get('doctrine')->getManager();
            
            $receipt = $emMULTI->getRepository('NvCargaBundle:Receipt')
                ->findOneBy(['number'=>$rec->getNumber(),'maincompany'=>$main]);
        }
        return $receipt;
        
    }
}
