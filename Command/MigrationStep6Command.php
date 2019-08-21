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


class MigrationStep6Command extends ContainerAwareCommand {
    protected function configure() {
        $this
        // the name of the command (the part after "app/console")
        ->setName('app:migration-step6')

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
        $output->writeln('INICIANDO MIGRACION PASO 6 ..');

        $emOLD = $this->getContainer()->get('doctrine')->getManager('migration');
        $emMULTI = $this->getContainer()->get('doctrine')->getManager();
        
        $homepage = $input->getArgument('homepage');
        
    
        $main2 = $emMULTI->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        $office = $emMULTI->getRepository('NvCargaBundle:Office')->findOneBy(['maincompany'=>$main2]);
        $type = $emMULTI->getRepository('NvCargaBundle:Agencytype')->findByName('MASTER');
        $themaster = $emMULTI->getRepository('NvCargaBundle:Agency')->findByType($type);
        
       
        $output->writeln('Creando MOVIMIENTOS a GUIAS... ');
        // AGREGAR LOS MOVIMIENTOS DE LAS GUIAS
        $movegs = $emOLD->getRepository('NvCargaBundle:Moveguides')->findAll();
        
        $count = 0;
        foreach ($movegs as $moveg) {
            $thegui = $emMULTI->getRepository('NvCargaBundle:Guide')->findOneBy(['number' => $moveg->getGuide()->getNumber(), 'maincompany'=>$main2]);
            $thest = $emMULTI->getRepository('NvCargaBundle:Guidestatus')->findOneBy(['name' => $moveg->getStatus()->getName()]);
            $thecom = $emMULTI->getRepository('NvCargaBundle:Localcompany')->findOneBy(['name' => $moveg->getCompany()->getName(), 'maincompany'=>$main2]);
            if (($thegui) && ($thest) && ($thecom)) {
                $moveg->setStatus($thest);
                $moveg->setGuide($thegui);
                $moveg->setCompany($thecom);
                $emMULTI->persist($moveg);
            }
            $count++;
            if ($count % 200 == 0) {
                $emMULTI->flush();
            }
        }
        $emMULTI->flush();
        $output->writeln('MOVIMIENTOS DE GUIAS creados... ');
    
        $output->writeln('Finalizado el PASO 6');
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
