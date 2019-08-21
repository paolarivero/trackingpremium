<?php
// src/Fuel/Bundle/Command/GetUserCommand.php
namespace NvCarga\Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use NvCarga\Bundle\Entity\Stepstatus;


class StepstatusCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
        // the name of the command (the part after "app/console")
        ->setName('app:stepstatus-create')

        // the short description shown while running "php app/console list"
        ->setDescription('Creación de los pasos para status')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('No tiene ayuda')
    ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('INICIANDO....');
        
        $em= $this->getContainer()->get('doctrine')->getManager();
        
        $mainall = $em->getRepository('NvCargaBundle:Maincompany')->findAll();
        $step1 = new Stepstatus();
        $step1->setName('Creado');
        $step1->setPercentage(20);
        $step1->setDescription('Se ha creado');
        $step1->setActive(true);
        
        $step2 = new Stepstatus();
        $step2->setName('Procesado');
        $step2->setPercentage(20);
        $step2->setDescription('Se ha procesado');
        $step2->setActive(true);
        
        $step3 = new Stepstatus();
        $step3->setName('En tránsito');
        $step3->setPercentage(20);
        $step3->setDescription('Se entregó al transportista');
        $step3->setActive(true);
        
        $step4 = new Stepstatus();
        $step4->setName('En aduana');
        $step4->setPercentage(20);
        $step4->setDescription('Está en aduana del país destino');
        $step4->setActive(true);
        
        $step5 = new Stepstatus();
        $step5->setName('Coordinado');
        $step5->setPercentage(20);
        $step5->setDescription('Listo para entrega');
        $step5->setActive(true);
        
        $steps = array();
        $steps[] = $step1;
        $steps[] = $step2;
        $steps[] = $step3;
        $steps[] = $step4;
        $steps[] = $step5;
        
        foreach ($mainall as $maincompany) {
            $output->writeln('Movimientos para la empresa ' . $maincompany->getName());
            foreach ($steps as $step) {
                $esta =  $em->getRepository('NvCargaBundle:Stepstatus')->findOneBy(['maincompany'=>$maincompany, 'name'=>$step->getName()]);
                $output->writeln('Movimiento:  ' . $step->getName());
                if (!$esta) {
                    $newstep = new Stepstatus();
                    $newstep->setName($step->getName());
                    $newstep->setDescription($step->getDescription());
                    $newstep->setPercentage($step->getPercentage());
                    $newstep->setActive(true);
                    $newstep->setMaincompany($maincompany);
                    $em->persist($newstep);
                }
            }
        }
        $em->flush();
        $output->writeln('Finalizado...');
        return true;
    }
}
