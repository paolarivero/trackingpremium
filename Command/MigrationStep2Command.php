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


class MigrationStep2Command extends ContainerAwareCommand {
    protected function configure() {
        $this
        // the name of the command (the part after "app/console")
        ->setName('app:migration-step2')

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
        $output->writeln('INICIANDO MIGRACION PASO 2 ..');

        $emMULTI = $this->getContainer()->get('doctrine')->getManager();
        $emOLD = $this->getContainer()->get('doctrine')->getManager('migration');
        
        $homepage = $input->getArgument('homepage');
        
    
        $main2 = $emMULTI->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        $office = $emMULTI->getRepository('NvCargaBundle:Office')->findOneBy(['maincompany'=>$main2]);
        $agencies = $emOLD->getRepository('NvCargaBundle:Agency')->findAll();
        $type = $emMULTI->getRepository('NvCargaBundle:Agencytype')->findByName('MASTER');
        
        $active = $emMULTI->getRepository('NvCargaBundle:Userstatus')->findOneByName('ACTIVO');
        $countagen = 0;
        $countcus = 0;
        $countuser = 0;
        $countpob = 0;
        $output->writeln('Creando AGENCIAS y USUARIOS');
        
        foreach ($agencies as $agency) {
            $status = $emMULTI->getRepository('NvCargaBundle:Agencystatus')->findOneByName($agency->getStatus()->getName());
            $type = $emMULTI->getRepository('NvCargaBundle:Agencytype')->findOneByName($agency->getType()->getName());
            // $master = $emMULTI->getRepository('NvCargaBundle:Agency')->findOneBy(['type'=>$type, 'maincompany' => $main2]);
            if ($agency->getParent()) {
                $agency->setParent($themaster);
            } else {
                $themaster = $agency;
            }
            $agency->setMaincompany($main2);
            $agency->setStatus($status);
            $agency->setType($type);
            $warehouse = $agency->getWarehouse();
            if (!$warehouse) {
                $warehouse = $emOLD->getRepository('NvCargaBundle:Warehouse')->find($agency->getId());
            }
            $warehouse->setMaincompany($main2);
            $agency->setWarehouse($warehouse);
            $warehouse->setAgency($agency);
            $city = $agency->getCity();
            $state = $city->getState();
            $country = $state->getCountry();
            $cag = $emMULTI->getRepository('NvCargaBundle:Country')->findOneByName($country->getName());
            $sag = $emMULTI->getRepository('NvCargaBundle:State')->findOneBy(['name'=>$state->getName(), 'country' => $cag]);
            $cityag = $emMULTI->getRepository('NvCargaBundle:City')->findOneBy(['name'=>$city->getName(), 'state' => $sag]);
            //$output->writeln($cityag->getName());
            $agency->setCity($cityag);
            $warehouse->setCity($cityag);
            $agency->setSharecustomer(true);
            $emMULTI->persist($agency);
            $countagen++;
            
            $emMULTI->persist($warehouse);
            $users = $emOLD->getRepository('NvCargaBundle:User')->findByAgency($agency);
            foreach ($users as $ouser) {
                $user = new User();
                $user->setUsername($ouser->getUsername());
                $user->setPobox($ouser->getPobox());
                $user->setEmail($ouser->getEmail());
                $user->setName($ouser->getName());
                $user->setLastname($ouser->getLastname());
                $user->setPassword($ouser->getPassword());
                $user->setSalt($ouser->getSalt());
                $user->setCreationdate($ouser->getCreationdate());
                
                $user->setStatus($active);
                $user->setAgency($agency);
                $user->setMaincompany($main2);
                if ($ouser->getPobox()) {
                    $pobox = $ouser->getPobox();
                    $creador = $emMULTI->getRepository('NvCargaBundle:User')->findOneByUsername($pobox->getCreateby()->getName());
                    $pobox->setUser($user);
                    $user->setPobox($pobox);
                    $typep = $emMULTI->getRepository('NvCargaBundle:Poboxtype')->findOneByName($pobox->getType()->getName());
                    $pobox->setType($typep);
                    $statusp = $emMULTI->getRepository('NvCargaBundle:Poboxstatus')->findOneByName($pobox->getStatus()->getName());
                    $pobox->setStatus($statusp);
                    $pobox->setWarehouse($warehouse);
                    $pobox->setCreateby($creador);
                    $pobox->setMaincompany($main2);
                    
                    $customer = $pobox->getCustomer();
                    $customer->setPobox($pobox);
                    $pobox->setCustomer($customer);
                    
                    $typec = $emMULTI->getRepository('NvCargaBundle:Customertype')->findOneByName($customer->getType()->getName());
                    $customer->setType($typec);
                    $statusc = $emMULTI->getRepository('NvCargaBundle:Customerstatus')->findOneByName($customer->getStatus()->getName());
                    $customer->setStatus($statusc);
                    $dirs = $customer->getBaddress();
                    foreach ($dirs as $dir) {
                        $dir->setCustomer($customer);
                        $customer->removeBaddress($dir);
                        $customer->addBaddress($dir);
                        $cityb = $dir->getCity();
                        $stateb = $cityb->getState();
                        $countryb = $stateb->getCountry();
                        $bcag = $emMULTI->getRepository('NvCargaBundle:Country')->findOneByName($countryb->getName());
                        $bsag = $emMULTI->getRepository('NvCargaBundle:State')->findOneBy(['name'=>$stateb->getName(), 'country' => $bcag]);
                        $bcityag = $emMULTI->getRepository('NvCargaBundle:City')->findOneBy(['name'=>$cityb->getName(), 'state' => $bsag]);
                        $dir->setCity($bcityag);
                        if ($dir == $customer->getAdrdefault()) {
                            $customer->setAdrdefault($dir);
                            $customer->setAdrmain($dir);
                        }
                        $emMULTI->persist($dir);
                    }
                    $customer->setAgency($themaster);
                    $customer->setMaincompany($main2);
                    $customer->setActive(true);
                    $emMULTI->persist($pobox);
                    $emMULTI->persist($customer);
                    $countcus++;
                    $countpob++;
                }
                foreach ($ouser->getRoles() as $role) {
                    $therole = $emMULTI->getRepository('NvCargaBundle:Role')->findOneBy(['name'=>$role->getName()]);
                    $therole->addUser($user);
                    $user->addUserRole($therole);
                }
                $rolemain = $emMULTI->getRepository('NvCargaBundle:Role')->findOneBy(['name'=>'ROLE_ADMIN_MAINCOMPANY']);
                if ($user->getUsername() == 'trackingpremium') {
                    $rolemain->addUser($user);
                    $user->addUserRole($rolemain);
                }
                $emMULTI->persist($user);
                $countuser++;
                $emMULTI->flush();
            }
            
            $tariffs = $emOLD->getRepository('NvCargaBundle:Tariff')->findByAgency($agency);
            foreach ($tariffs as $tariff) {
                $tariff->setAgency($agency);
                // $output->writeln('AGENCIA: ' . $agency->getName() . '; TARIFA: ' . $tariff->getName());
                $name = $tariff->getService()->getShippingtype()->getName();
                // $output->writeln('NOMBRE: ' . $name);
                $servicio = $emMULTI->getRepository('NvCargaBundle:Shippingtype')->findOneBy(['name'=>$name]);
                $tariff->setShippingtype($servicio);
                $medida = $emMULTI->getRepository('NvCargaBundle:Measure')->findOneBy(['name'=>$tariff->getMeasure()->getName(), 'maincompany'=>$main2]);
                $tariff->setMeasure($medida);
                $tariff->setService(null);
                if ($tariff->getRegion()) {
                    $name = $tariff->getRegion()->getName();
                    $pos1 = strpos(strtolower($name), 'todas');
                    $pos2 = strpos(strtolower($name), strtolower($country->getName()));
                    if (($pos1 === false) || ($pos2 === false)) {
                        $thecountry = $emMULTI->getRepository('NvCargaBundle:Country')->findOneBy(['name'=>$tariff->getRegion()->getCountry()->getName()]);
                        $region = $emMULTI->getRepository('NvCargaBundle:Region')->findOneBy(['name'=>$tariff->getRegion()->getName(), 'maincompany' => $main2, 'country'=>$thecountry]);
                        
                    } else {
                        $region = $emMULTI->getRepository('NvCargaBundle:Region')->findOneBy(['name'=>'Todas las ciudades de ' . $thecountry->getName(), 'maincompany' => $main2, 'country'=>$thecountry]);
                    }
                    $tariff->setRegion($region);
                    $emMULTI->persist($tariff);
                } else {
                    $countries = $main2->getCountries()->toArray();
                    foreach ($countries as $thecountry) {
                        $region = $emMULTI->getRepository('NvCargaBundle:Region')->findOneBy(['name'=>'Todas las ciudades de ' . $thecountry->getName(), 'maincompany' => $main2, 'country'=>$thecountry]);
                        $tariff->setRegion($region);
                        $emMULTI->persist($tariff);
                    }
                }
            }
        }
        $main2->setCountagencies($countagen);
        $main2->setCountusers($countuser);
        $main2->setCountpoboxes($countpob);
        $emMULTI->flush();
        
        // AGREGAR PERFILES DE USUARIOS
        $profiles = $emOLD->getRepository('NvCargaBundle:Profile')->findAll();
        if (count($profiles) == 0) {
            $output->writeln('NO HAY PERFILES ACTUALMENTE');
            $mainbase = $emMULTI->getRepository('NvCargaBundle:Maincompany')->find(1);
            $pexits = $emMULTI->getRepository('NvCargaBundle:Profile')->findByMaincompany($mainbase);
            foreach ($pexits as $profile) {
                $theprof = new Profile();
                $theprof->setName($profile->getName());
                $theprof->setMaincompany($main2);
                $theprof->setDescription($profile->getDescription());
                foreach ($profile->getRoles() as $role) {
                    $therole = $emMULTI->getRepository('NvCargaBundle:Role')->findOneBy(['name'=>$role->getName()]);
                    $theprof->addRole($therole);
                }
                // $output->writeln('NAME:' . $theprof->getName());
                $emMULTI->persist($theprof);
            }
        } else {
            $output->writeln('SI HAY PERFILES ACTUALMENTE');
            foreach ($profiles as $profile) {
                $theprof = new Profile();
                $theprof->setName($profile->getName());
                $theprof->setMaincompany($main2);
                $theprof->setDescription($profile->getDescription());
                foreach ($profile->getRoles() as $role) {
                    $therole = $emMULTI->getRepository('NvCargaBundle:Role')->findOneBy(['name'=>$role->getName()]);
                    $theprof->addRole($therole);
                }
                //$output->writeln('SI NAME:' . $theprof->getName());
                $emMULTI->persist($theprof);
            }
        }
        $emMULTI->flush();
        
        $output->writeln('Creando clientes..');
        $customers = $emOLD->getRepository('NvCargaBundle:Customer')->findAll();
        foreach ($customers as $customer) {
            if (!$customer->getPobox()) {
                $typec = $emMULTI->getRepository('NvCargaBundle:Customertype')->findOneByName($customer->getType()->getName());
                if (!$typec) {
                    $typec = $emMULTI->getRepository('NvCargaBundle:Customertype')->findOneByName('Persona');
                }
                $customer->setType($typec);
                $statusc = $emMULTI->getRepository('NvCargaBundle:Customerstatus')->findOneByName($customer->getStatus()->getName());
                $customer->setStatus($statusc);
                $dirs = $customer->getBaddress();
                foreach ($dirs as $dir) {
                    if ($dir == $customer->getAdrdefault()) {
                        $customer->setAdrdefault($dir);
                        $customer->setAdrmain($dir);
                    }
                    $dir->setCustomer($customer);
                    $customer->removeBaddress($dir);
                    $customer->addBaddress($dir);
                    if ($dir->getCity()) {
                        $cityb = $dir->getCity();
                        $stateb = $cityb->getState();
                        $countryb = $stateb->getCountry();
                        $bcag = $emMULTI->getRepository('NvCargaBundle:Country')->findOneByName($countryb->getName());
                        $bsag = $emMULTI->getRepository('NvCargaBundle:State')->findOneBy(['name'=>$stateb->getName(), 'country' => $bcag]);
                        $bcityag = $emMULTI->getRepository('NvCargaBundle:City')->findOneBy(['name'=>$cityb->getName(), 'state' => $bsag]);
                        $dir->setCity($bcityag);
                    }
                    $emMULTI->persist($dir);
                }
                $customer->setAgency($themaster);
                $customer->setMaincompany($main2);
                $customer->setActive(true);
                $emMULTI->persist($customer);
                $countcus++;
            }
        }
        $main2->setCountcustomers($countpob);
        $emMULTI->flush();
        $output->writeln('Finalizado el PASO 2');
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
