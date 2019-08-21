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
use NvCarga\Bundle\Entity\Profile;


class MigrationStep4Command extends ContainerAwareCommand {
    protected function configure() {
        $this
        // the name of the command (the part after "app/console")
        ->setName('app:migration-step4')

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
        $output->writeln('INICIANDO MIGRACION PASO 4 ..');

        $emMULTI = $this->getContainer()->get('doctrine')->getManager();
        $emOLD = $this->getContainer()->get('doctrine')->getManager('migration');
        
        $homepage = $input->getArgument('homepage');
        
    
        $main2 = $emMULTI->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        $office = $emMULTI->getRepository('NvCargaBundle:Office')->findOneBy(['maincompany'=>$main2]);
        $type = $emMULTI->getRepository('NvCargaBundle:Agencytype')->findByName('MASTER');
        $themaster = $emMULTI->getRepository('NvCargaBundle:Agency')->findByType($type);
        
        // RECORRER NUEVAMENTE LAS AGENCIAS PARA CREAR LAS GUÍAS
        $agencies = $emOLD->getRepository('NvCargaBundle:Agency')->findAll();
        $output->writeln('CREANDO las Guías... ');
        $count=0;
        foreach ($agencies as $agency) {
            $theagency = $emMULTI->getRepository('NvCargaBundle:Agency')->findOneBy(['name'=>$agency->getName(), 'maincompany'=>$main2]);
            $guides = $emOLD->getRepository('NvCargaBundle:Guide')->findByAgency($agency);
            foreach ($guides as $guide) {
                $theguide = new Guide();
                $countryfrom = $emMULTI->getRepository('NvCargaBundle:Country')->findOneByName($guide->getCountryfrom()->getName());
                $theguide->setCountryfrom($countryfrom);
                $countryto = $emMULTI->getRepository('NvCargaBundle:Country')->findOneByName($guide->getCountryto()->getName());
                $theguide->setCountryto($countryto);
                $theguide->setAgency($theagency);
                $dir = $this->getDir($guide->getAddressee(), $main2);
                $cus = $this->getCustomer($guide->getSender(),$main2);
                $theguide->setAddressee($dir);
                $theguide->setSender($cus);
                $tariff = $emMULTI->getRepository('NvCargaBundle:Tariff')->findOneBy(['name'=>$guide->getTariff()->getName(), 'agency'=>$theagency]);
                $theguide->setTariff($tariff);
                $cod = $emMULTI->getRepository('NvCargaBundle:COD')->findOneByName($guide->getCOD()->getName());
                $theguide->setCOD($cod);
                $paidtype = $emMULTI->getRepository('NvCargaBundle:Paidtype')->findOneBy(['name'=>$guide->getPaidtype()->getName(), 'maincompany'=>$main2]);
                $theguide->setPaidtype($paidtype);
                if ($guide->getShippingtype()) {
                    $shippingtype = $emMULTI->getRepository('NvCargaBundle:Shippingtype')->findOneByName($guide->getShippingtype()->getName());
                } else {
                    $shippingtype = $emMULTI->getRepository('NvCargaBundle:Shippingtype')->findOneByName('Aéreo');
                }
                $theguide->setShippingtype($shippingtype);
                $theguide->setConsol(null);
                $theguide->setBag(null);
                $theguide->setBill(null);
                $guser = $guide->getProcessedby();
                $guidecuser = $emMULTI->getRepository('NvCargaBundle:User')->findOneBy(['username' => $guser->getUsername(), 'maincompany'=>$main2]);
                $theguide->setProcessedby($guidecuser);
                if ($guide->getMasterec()) {
                    $master = $emMULTI->getRepository('NvCargaBundle:Receipt')->findOneBy(['number' => $guide->getMasterec()->getNumber(), 'maincompany'=>$main2]);
                    $theguide->setMasterec($master);
                    $master->setGuide($guide);
                } else {
                    $theguide->setMasterec(null);
                }
                
                foreach ($guide->getReceipts() as $receipt) {
                    $therec = $emMULTI->getRepository('NvCargaBundle:Receipt')->findOneBy(['number' => $receipt->getNumber(), 'maincompany'=>$main2]);
                    $therec->setGuide($theguide);
                    $guide->removeReceipt($receipt);
                    $theguide->addReceipt($therec);
                }
                $theguide->setNumber($guide->getNumber());
                $theguide->setCreationdate($guide->getCreationdate());
                $theguide->setContain($guide->getContain());
                $theguide->setEmailnoti($guide->getEmailnoti());
                $theguide->setMobilnoti($guide->getMobilnoti());
                $theguide->setImageData($guide->getImageData());
                $theguide->setImageType($guide->getImageType());
                $theguide->setImageSize($guide->getImageSize());
                $theguide->setRealweight($guide->getRealweight());
                $theguide->setPieces($guide->getPieces());
                
                $theguide->setDeclared($guide->getDeclared());
                $theguide->setTaxPer($guide->getTaxPer());
                $theguide->setTaxPaid($guide->getTaxPaid());
                $theguide->setOrdernumber($guide->getOrdernumber());
                $theguide->setInsuranceAmount($guide->getInsuranceAmount());
                $theguide->setInsurancePer($guide->getInsurancePer());
                $theguide->setInsurancePaid($guide->getInsurancePaid());
                $theguide->setDiscount($guide->getDiscount());
                $theguide->setOtherfees($guide->getOtherfees());
                $theguide->setFreight($guide->getFreight());
                
                $theguide->setVolfreight($guide->getVolfreight());
                $theguide->setPaidweight($guide->getPaidweight());
                $theguide->setMeasurevalue($guide->getMeasurevalue());
                $theguide->setTotalpaid($guide->getTotalpaid());
                $theguide->setDownpayment($guide->getDownpayment());
                $theguide->setRoundmeasure($guide->getRoundmeasure());
                $theguide->setMaincompany($main2);
                $emMULTI->persist($theguide);
                $count++;
                if ($count % 100 == 0) {
                    $emMULTI->flush();
                }
            }
            
        }
       
        $main2->setCountguides($count);
        $output->writeln('Guías Creadas: ' . $count);
        
        $emMULTI->flush();
        $output->writeln('Finalizado el PASO 4');
        
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
