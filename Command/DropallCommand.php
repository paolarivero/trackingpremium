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


class DropallCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
        // the name of the command (the part after "app/console")
        ->setName('app:dropall')

        // the short description shown while running "php app/console list"
        ->setDescription('Elimina todos los datos de una empresa')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('Elimina todos los datos de una empresa')
        ->addArgument('homepage', InputArgument::REQUIRED, 'El homepage de la empresa')
    ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $em = $this->getContainer()->get('doctrine')->getManager();
        $homepage = $input->getArgument('homepage');
        
        $alerts = $em->getRepository('NvCargaBundle:Alert')->findAll();
        foreach ($alerts as $alert) {
            if ($alert->getBaddress()) {
                $customer = $alert->getBaddress()->getCustomer();
                if (!$alert->getMaincompany()) {
                    $alert->setMaincompany($customer->getMaincompany());
                }
            }
        }
        $em->flush();
        
        $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        $output->writeln('Empresa: ' . $homepage );
        $output->writeln('Empresa: ' . $maincompany->getName());
        $steps = $em->getRepository('NvCargaBundle:Stepstatus')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('INICIANDO ELIMINACIÖN de ENTIDADES..');
        $output->writeln('ELIMINACIÖN de STATUS');
        foreach ($steps as $step) {
            $statusreceipt = $em->getRepository('NvCargaBundle:Statusreceipt')->findBy(['step'=>$step]);
            foreach ($statusreceipt as $statusrec) {
                $em->remove($statusrec);
            }
            $statuswhrec = $em->getRepository('NvCargaBundle:Statuswhrec')->findBy(['step'=>$step]);
            foreach ($statuswhrec as $statuswh) {
                $em->remove($statuswh);
            }
            $statusguide = $em->getRepository('NvCargaBundle:Statusguide')->findBy(['step'=>$step]);
            foreach ($statusguide as $statusgd) {
                $em->remove($statusgd);
            }
            $statusconsol = $em->getRepository('NvCargaBundle:Statusconsol')->findBy(['step'=>$step]);
            foreach ($statusconsol as $statuscon) {
                $em->remove($statuscon);
            }
            $em->remove($step);
        }
        $em->flush();
        $guides = $em->getRepository('NvCargaBundle:Guide')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de MOVIMIENTOS DE GUIAS');
        foreach ($guides as $guide) {
            $moves = $em->getRepository('NvCargaBundle:Moveguides')->findBy(['guide'=>$guide]);
            foreach ($moves as $move) {
                $guide->removeMove($move);
                $move->setGuide(null);
                $em->remove($move);
            }
        }
        $em->flush();
        $consols = $em->getRepository('NvCargaBundle:Consolidated')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de MOVIMIENTOS DE CONSOLIDADOS');
        foreach ($consols as $consol) {
            $moves = $em->getRepository('NvCargaBundle:Moveconsols')->findBy(['consolidated'=>$consol]);
            foreach ($moves as $move) {
                $consol->removeMove($move);
                $move->setConsolidated(null);
                $em->remove($move);
            }
        }
        $em->flush();
        $guides = $em->getRepository('NvCargaBundle:Guide')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de Servicios Adicionales a Guías');
        foreach ($guides as $guide) {
            $servgs = $em->getRepository('NvCargaBundle:Servtoguide')->findBy(['guide'=>$guide]);
            foreach ($servgs as $serv) {
                $em->remove($serv);
            }
        }
        $em->flush();
        $bags = $em->getRepository('NvCargaBundle:Bag')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de Bolsas');
        foreach ($bags as $bag) {
            $guides = $bag->getGuides();
            foreach ($guide as $guide) {
                $guide->setBag(null);
                $bag->removeGuide($guide);
            }
            $em->remove($bag);
        }
        $em->flush();
        $guides = $em->getRepository('NvCargaBundle:Guide')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de Pagos a Guías');
        foreach ($guides as $guide) {
            $payments = $em->getRepository('NvCargaBundle:Payment')->findBy(['guide'=>$guide]);
            foreach ($payments as $pay) {
                $payment->setGuide(null);
                $payment->setCustomer(null);
                $payment->setAccount(null);
                $em->remove($payment);
            }
        }
        $em->flush();
        $guides = $em->getRepository('NvCargaBundle:Guide')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de Track a Guías');
        foreach ($guides as $guide) {
            $tracks = $em->getRepository('NvCargaBundle:Trackguide')->findBy(['guide'=>$guide]);
            foreach ($tracks as $track) {
                $track->setGuide(null);
                $em->remove($track);
            }
        }
        $em->flush();
        $bills = $em->getRepository('NvCargaBundle:Bill')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de Facturas');
        foreach ($bills as $bill) {
            $guides = $bill->getGuides();
            foreach ($guides as $guide) {
                $guide->setBill(null);
            }
            $bill->setCustomer(null);
            $bill->setCOD(null);
            $bill->setPaidtype(null);
            $payments = $bill->getPayments();
            foreach ($payments as $pay) {
                $pay->setBill(null);
                $bill->removePayment($pay);
                $em->remove($pay);
            }
            $em->remove($bill);
        }
        $em->flush();
        $consols = $em->getRepository('NvCargaBundle:Consolidated')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de Consolidados');
        foreach ($consols as $consol) {
            $guides = $consol->getGuides();
            foreach ($guides as $guide) {
                $guide->setConsol(null);
                $consol->removeGuide($guide);
            }
            $em->remove($consol);
        }
        $em->flush();
        $guides = $em->getRepository('NvCargaBundle:Guide')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de Guías');
        foreach ($guides as $guide) {
            $receipts = $guide->getReceipts();
            $profile = $guide->getWhrec();
            foreach ($receipts as $receipt) {
                $receipt->setGuide(null);
                $guide->removeReceipt($receipt);
                $guide->setTariff(null);
            }
            if ($profile) {
                $profile->setGuide(null);
                $guide->setWhrec(null);
            }
            $em->remove($guide);
        }
        $em->flush();
        
        
        
        $alerts = $em->getRepository('NvCargaBundle:Alert')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de Alertas');
        foreach ($alerts as $alert) {
            $alert->setReceipt(null);
            $em->remove($alert);
        }
        $em->flush();
        
        $receipts = $em->getRepository('NvCargaBundle:Receipt')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de PAQUETES');
        foreach ($receipts as $receipt) {
            if ($receipt->getMaster()) {
                $receipt->setMaster(null);
            }
        }
        foreach ($receipts as $receipt) {
            $em->remove($receipt);
        }
        $em->flush();
        $profiles = $em->getRepository('NvCargaBundle:WHrec')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de RECIBOS');
        foreach ($profiles as $profile) {
            $em->remove($profile);
        }
        $em->flush();
        $users = $em->getRepository('NvCargaBundle:User')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de MENSAJES');
        foreach ($users as $user) {
            $messages = $em->getRepository('NvCargaBundle:Message')->findBy(['sender'=>$user]);
            foreach ($messages as $men) {
                $men->setSender(null);
                $men->setReceiver(null);
                $em->remove($men);
            }
        }
        $em->flush();
        $customers = $em->getRepository('NvCargaBundle:Customer')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de CLIENTES');
        foreach ($customers as $customer) {
            $customer->setAdrmain(null);
            $customer->setAdrdefault(null);
        }
        $em->flush();
        foreach ($customers as $customer) {
            $dirs = $customer->getBaddress();
            foreach ($dirs as $dir) {
                $dir->setCustomer(null);
                $customer->removeBaddress($dir);
                $em->remove($dir);
            } 
            $pobox=$customer->getPobox();
            if ($pobox) {
                $customer->setPobox(null);
                $pobox->setCustomer(null);
            }
            $em->remove($customer);
        }
        $em->flush();
        $profiles = $em->getRepository('NvCargaBundle:Profile')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de PERFILES');
        foreach ($profiles as $profile) {
            $em->remove($profile);
        }
        $em->flush();
        $agencies = $em->getRepository('NvCargaBundle:Agency')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de AGENCIAS');
        foreach ($agencies as $agency) {
            $users = $em->getRepository('NvCargaBundle:User')->findBy(['maincompany'=>$maincompany, 'agency'=>$agency]);
            $output->writeln('ELIMINACIÖN de USUARIOS de agencia ' . $agency->getName());
            foreach ($users as $user) {
                $pobox = $user->getPobox();
                $roles = $user->getRoles();
                foreach ($roles as $role) {
                    $role->removeUser($user);
                }
                if ($pobox) {
                    $pobox->setUser(null);
                    $pobox->setWarehouse(null);
                    $user->setPobox(null);
                    $em->flush();
                    $em->remove($pobox);
                }
                $agency->removeUser($user);
                $em->remove($user);
                $em->flush();
            }
            
            $tariffs = $em->getRepository('NvCargaBundle:Tariff')->findBy(['agency'=>$agency]);
            $output->writeln('ELIMINACIÖN de TARIFAS de agencia ' . $agency->getName());
            foreach ($tariffs as $tariff) {
                $tariff->setRegion(null);
                $em->remove($tariff);
            }
            $warehouse=$agency->getWarehouse();
            $warehouse->setAgency(null);
            $agency->setWarehouse(null);
            $em->remove($warehouse);
            $em->remove($agency);
            $em->flush();
        }
        
        $adservices = $em->getRepository('NvCargaBundle:Adservice')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de SERVICIOS ADICIONALES');
        foreach ($adservices as $adservice) {
            $list = $adservice->getDependofMe();
            foreach ($list as $serv) {
                $theserv->removeDependofMe($serv);
            }
        }
        foreach ($adservices as $adservice) {
           $em->remove($adservice);
        }
        $em->flush();
        $news = $em->getRepository('NvCargaBundle:News')->findBy(['maincompany'=>$maincompany]);
        $output->writeln('ELIMINACIÖN de NOTICIAS');
        foreach ($news as $new) {
            $em->remove($new);
        }
        $em->flush();
        $output->writeln('ELIMINACIÖN de OFICINAS');
        $office = $em->getRepository('NvCargaBundle:Office')->findOneBy(['maincompany'=>$maincompany]);
        if ($office) {
            $em->remove($office);
        }
        $output->writeln('ELIMINACIÖN de CARRIERS');
        $carriers = $em->getRepository('NvCargaBundle:Carrier')->findBy(['maincompany'=>$maincompany]);
        foreach ($carriers as $carrier) {
            $em->remove($carrier);
        }
        $output->writeln('ELIMINACIÖN de TIPOS DE PAGO');
        $ptypes = $em->getRepository('NvCargaBundle:Paidtype')->findBy(['maincompany'=>$maincompany]);
        foreach ($ptypes as $ptype) {
            $em->remove($ptype);
        }
        $output->writeln('ELIMINACIÖN de MEDIDAS');
        $measures = $em->getRepository('NvCargaBundle:Measure')->findBy(['maincompany'=>$maincompany]);
        foreach ($measures as $measure) {
            $em->remove($measure);
        }
        $output->writeln('ELIMINACIÖN de CUENTAS');
        $accounts = $em->getRepository('NvCargaBundle:Account')->findBy(['maincompany'=>$maincompany]);
        foreach ($accounts as $account) {
            $em->remove($account);
        }
        $output->writeln('ELIMINACIÖN de TERMINOS');
        $terconds = $em->getRepository('NvCargaBundle:Termcond')->findBy(['maincompany'=>$maincompany]);
        foreach ($terconds as $tercond) {
            $em->remove($tercond);
        }
        $output->writeln('ELIMINACIÖN de ETIQUETAS');
        $labels = $em->getRepository('NvCargaBundle:Labelconf')->findBy(['maincompany'=>$maincompany]);
        foreach ($labels as $label) {
            $em->remove($label);
        }
        $output->writeln('ELIMINACIÖN de FORMATOS');
        $format = $em->getRepository('NvCargaBundle:Format')->findOneBy(['maincompany'=>$maincompany]);
        if ($format) {
            $format->setMaincompany(null);
        }
        $em->flush();
        $format = $em->getRepository('NvCargaBundle:Format')->findOneBy(['maincompany'=>$maincompany]);
        if ($format) {
            $em->remove($format);
        }
        $em->flush();
        $output->writeln('ELIMINACIÖN de REGIONES');
        $regions = $em->getRepository('NvCargaBundle:Region')->findBy(['maincompany'=>$maincompany]);
        foreach ($regions as $region) {
            $em->remove($region);
        }
        $output->writeln('ELIMINACIÖN de COMPAÑIAS LOCALES');
        $companies = $em->getRepository('NvCargaBundle:Localcompany')->findBy(['maincompany'=>$maincompany]);
        foreach ($companies as $company) {
            $em->remove($company);
        }
        $em->flush();
        $output->writeln('ELIMINACIÖN de COMPAÑIAS');
        $companies = $em->getRepository('NvCargaBundle:Company')->findBy(['maincompany'=>$maincompany]);
        foreach ($companies as $company) {
            $em->remove($company);
        }
        $em->flush();
        $output->writeln('ELIMINACIÖN de PAISES');
        $countries = $maincompany->getCountries()->toArray();
        foreach ($countries as $country) {
            $maincompany->removeCountry($country);
        }
        $em->remove($maincompany);
        $em->flush();
        
        return true;
    }
}
