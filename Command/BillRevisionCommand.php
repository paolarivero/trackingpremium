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


class BillRevisionCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
        // the name of the command (the part after "app/console")
        ->setName('app:billrevision')

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
        $output->writeln('INICIANDO REVISION de las Facturas ..');

        $emOLD = $this->getContainer()->get('doctrine')->getManager('migration');
        $emMULTI = $this->getContainer()->get('doctrine')->getManager();
        
        $homepage = $input->getArgument('homepage');
    
        $main2 = $emMULTI->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        
        $bills = $emOLD->getRepository('NvCargaBundle:Bill')->findAll();
        $count = 0;
        foreach ($bills as $bill) {
            $thebill = $emMULTI->getRepository('NvCargaBundle:Bill')->findOneBy(['maincompany'=>$main2, 'number'=>$bill->getNumber()]);
            if ($thebill) {
                $thebill->setStatus($bill->getStatus());
            }
        }
        $emMULTI->flush();
    }
}
