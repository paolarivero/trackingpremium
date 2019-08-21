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


class MigrationStep3Command extends ContainerAwareCommand {
    protected function configure() {
        $this
        // the name of the command (the part after "app/console")
        ->setName('app:migration-step3')

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
        $output->writeln('INICIANDO MIGRACION PASO 3 ..');

        $emMULTI = $this->getContainer()->get('doctrine')->getManager();
        $emOLD = $this->getContainer()->get('doctrine')->getManager('migration');
        
        $homepage = $input->getArgument('homepage');
        
    
        $main2 = $emMULTI->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        $office = $emMULTI->getRepository('NvCargaBundle:Office')->findOneBy(['maincompany'=>$main2]);
        $type = $emMULTI->getRepository('NvCargaBundle:Agencytype')->findByName('MASTER');
        $themaster = $emMULTI->getRepository('NvCargaBundle:Agency')->findOneByType($type);
        
        
        
        // MENSAJES EXISTENTES, deben estar CREADOS LOS USUARIOS
        $messages = $emOLD->getRepository('NvCargaBundle:Message')->findAll();
        foreach ($messages as $message) {
            $from = $emMULTI->getRepository('NvCargaBundle:User')->findOneBy(['username' => $message->getSender()->getUsername(), 'maincompany'=>$main2]);
            $to = $emMULTI->getRepository('NvCargaBundle:User')->findOneBy(['username' => $message->getReceiver()->getUsername(), 'maincompany'=>$main2]);
            $message->setSender($from);
            $message->setReceiver($to);
            $emMULTI->persist($message);
        }
        $emMULTI->flush();
        
        
        $output->writeln('Creando  los recibos');
        // RECORRER NUEVAMENTE LAS AGENCIAS PARA CREAR LOS RECIBOS
        $agencies = $emOLD->getRepository('NvCargaBundle:Agency')->findAll();
        $count = 0;
        foreach ($agencies as $agency) {
            $theagency = $emMULTI->getRepository('NvCargaBundle:Agency')->findOneBy(['name'=>$agency->getName(), 'maincompany'=>$main2]);
            //$output->writeln('Creando los recibos MASTER de ' . $theagency->getName());
            // RECIBOS EXISTENTES
            $receipts = $emOLD->getRepository('NvCargaBundle:Receipt')->findByAgency($agency);
            
            foreach ($receipts as $receipt) {
                //$output->writeln('Recibo: ' . $receipt->getNumber());
                if (!$receipt->getType() || ($receipt->getType()->getName() == 'Master')) {
                    //$output->writeln('Recibo: ' . $receipt->getNumber());
                    $receipt->setAgency($theagency);
                    $ruser = $receipt->getReceiptdBy();
                    $recuser = $emMULTI->getRepository('NvCargaBundle:User')->findOneBy(['username' => $ruser->getUsername(), 'maincompany'=>$main2]);
                    //$output->writeln('USUARIO: ' . $receipt->getReceiptdBy()->getUsername() . '=> NUEVO: ' . $recuser->getUsername());
                    //$output->writeln('USUARIO: ' . $ruser->getUsername());
                    $receipt->setReceiptdBy($recuser);
                    $dir = $this->getDir($receipt->getReceiver(), $main2);
                    $receipt->setReceiver($dir);
                    $cus = $this->getCustomer($receipt->getShipper(),$main2);
                    $receipt->setShipper($cus);
                    $carrier = $emMULTI->getRepository('NvCargaBundle:Carrier')->findOneBy(['name'=>$receipt->getCarrier()->getName(), 'maincompany'=>$main2]);
                    if ($carrier) {
                        $receipt->setCarrier($carrier);
                    } else {
                        $receipt->setCarrier(null);
                    }
                    if ($receipt->getStatus()) {
                        $recst = $emMULTI->getRepository('NvCargaBundle:Receiptstatus')->findOneByName($receipt->getStatus()->getName());
                    } else {
                        $recst = $emMULTI->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Por procesar');
                    }
                    $receipt->setStatus($recst);
                    $rectype = $emMULTI->getRepository('NvCargaBundle:Receipttype')->findOneByName('Master');
                    $receipt->setType($rectype);
                    $receipt->setGuide(null);
                    $receipt->setMaster(null);
                    $receipt->setMaincompany($main2);
                    if (!$receipt->getPacktype()) {
                        $receipt->setPacktype('Caja');
                    }
                    if ($receipt->getNpack() == 0) {
                        $receipt->setNpack(1);
                    }
                    $emMULTI->persist($receipt);
                    if ($receipt->getStatus()->getName() != 'Anulado') {
                        $count++;
                    }
                    if ($count % 100 == 0) {
                        $emMULTI->flush();
                    }
                }
            
            }
            $emMULTI->flush();
            
            //$output->writeln('Creados los recibos MASTER');
            //$output->writeln('Creando los recibos NORMALES de ' . $theagency->getName());
            // unset($receipts);
            // $otherreceipts = $emOLD->getRepository('NvCargaBundle:Receipt')->findByAgency($agency);
            foreach ($receipts as $receipt) {
                // $output->writeln('Recibo: ' . $receipt->getNumber());
                if (($receipt->getType()) && ($receipt->getType()->getName() != 'Master')) {
                    //$output->writeln('Recibo: ' . $receipt->getNumber());
                    $receipt->setAgency($theagency);
                    $ruser = $receipt->getReceiptdBy();
                    $recuser = $emMULTI->getRepository('NvCargaBundle:User')->findOneBy(['username' => $ruser->getUsername(), 'maincompany'=>$main2]);
                    $receipt->setReceiptdBy($recuser);
                    $dir = $this->getDir($receipt->getReceiver(), $main2);
                    $receipt->setReceiver($dir);
                    $cus = $this->getCustomer($receipt->getShipper(),$main2);
                    $receipt->setShipper($cus);
                    if ($receipt->getCarrier()) {
                        $carrier = $emMULTI->getRepository('NvCargaBundle:Carrier')->findOneBy(['name'=>$receipt->getCarrier()->getName(), 'maincompany'=>$main2]);
                    } else {
                        $carrier = null;
                    }
                    if ($carrier) {
                        $receipt->setCarrier($carrier);
                    } else {
                        $receipt->setCarrier(null);
                    }
                    if ($receipt->getStatus()) {
                        $recst = $emMULTI->getRepository('NvCargaBundle:Receiptstatus')->findOneByName($receipt->getStatus()->getName());
                    } else {
                        $recst = $emMULTI->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Por procesar');
                    }
                    $receipt->setStatus($recst);
                    $rectype =  $emMULTI->getRepository('NvCargaBundle:Receipttype')->findOneByName($receipt->getType()->getName());
                    $receipt->setType($rectype);
                    $receipt->setGuide(null);
                    $receipt->setMaster(null);
                    $receipt->setMaincompany($main2);
                    if (!$receipt->getPacktype()) {
                        $receipt->setPacktype('Caja');
                    }
                    if ($receipt->getNpack() == 0) {
                        $receipt->setNpack(1);
                    }
                    if ($receipt->getMaster()) {
                        $master= $emMULTI->getRepository('NvCargaBundle:Receipt')->findOneBy(['number'=>$receipt->getMaster()->getNumber(), 'maincompany'=>$main2]);
                        $receipt->setMaster($master);
                    }
                    $emMULTI->persist($receipt);
                    if ($receipt->getStatus()->getName() != 'Anulado') {
                        $count++;
                    }
                    if ($count % 100 == 0) {
                        $emMULTI->flush();
                    }
                }
            }
            $emMULTI->flush();
            //$output->writeln('Creados los recibos normales de ' . $theagency->getName());
        }
        $output->writeln('Creados los recibos');
        $main2->setCountreceipts($count);
        
        
        
        // ALERTAS EXISTENTES, DEBEN ESTAR AGREGADOS LOS RECIBOS...
        $alerts = $emOLD->getRepository('NvCargaBundle:Alert')->findAll();
        $count =0;
        foreach ($alerts as $alert) {
            if ($alert->getShippingtype()) {
                $envio = $emMULTI->getRepository('NvCargaBundle:Shippingtype')->findOneByName($alert->getShippingtype()->getName());
            } else {
                $envio = null;
            }
            if ($envio) {
                $alert->setShippingtype($envio);
            } else {
                $alert->setShippingtype(null);
            }
            if ($alert->getCarrier()) {
                $carrier = $emMULTI->getRepository('NvCargaBundle:Carrier')->findOneBy(['name'=>$alert->getCarrier()->getName(), 'maincompany'=>$main2]);
            } else {
                $carrier = null;
            }
            if ($carrier) {
                $alert->setCarrier($carrier);
            } else {
                $alert->setCarrier(null);
            }
            $pobox = $emMULTI->getRepository('NvCargaBundle:Pobox')->findOneBy(['number'=>$alert->getPobox()->getNumber(), 'maincompany'=>$main2]);
            $alert->setPobox($pobox);
            $dir = $this->getDir($alert->getBaddress(), $main2);
            $alert->setBaddress($dir);
            if ($alert->getReceipt()) {
                $receipt = $this->getReceipt($alert->getReceipt(), $main2);
                $alert->setReceipt($receipt); 
            }
            $alert->setMaincompany($main2);
            $emMULTI->persist($alert);
            $count++;
        }
        $main2->setCountalerts($count);
        $emMULTI->flush();
        $output->writeln('Finalizado el PASO 3');
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
