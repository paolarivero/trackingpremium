<?php

namespace NvCarga\Bundle\Controller\api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Prefix;

use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

use NvCarga\Bundle\Entity\Consolidated;
use NvCarga\Bundle\Entity\Moveguides;
use NvCarga\Bundle\Entity\Moveconsols;


/**
 * Consolidated controller for API.
 * @Prefix("/consolidated")
 */
class ConsolAPIController extends FOSRestController
{
    /**
     * Get all the consolidated
     * @return array
     * @Get("/index")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction(Request $request){

	$dataget = $request->query->all();
	$pagenumber = $dataget['pagenumber'];
	$pagesize = $dataget['pagesize'];
	
	if ($pagenumber) {
		$pagenumber = intval($pagenumber);
	} else 	{
		$pagenumber = 1;
	}
	if ($pagesize) {
		$pagesize = intval($pagesize);
	} else 	{
		$pagesize = 100;
	}

	if (($pagesize < 100) && ($pagesize > 0)) {
		$defaultsize = $pagesize;
	} else {
		$defaultsize = 100;
	}

	$page = (isset($pagenumber) && $pagenumber > 0 ? $pagenumber : 1);

	$offset = ($defaultsize * ($page - 1));
	$criteria = \Doctrine\Common\Collections\Criteria::create()
   	 	->setMaxResults($defaultsize)
    		->setFirstResult($offset);

	$expr = $criteria->expr();
	$consols = $this->getDoctrine()->getRepository('NvCargaBundle:Consolidated')
   		 ->matching($criteria);
	$numconsol = $consols->count();

	$result = array();
	$counter = 0;
        foreach ($consols as $consol) {
	    $result[$counter]['id'] = $consol->getId();
	    $result[$counter]['number'] = $consol->getNumber();
	    $result[$counter]['date'] = $consol->getMoves()->first()->getMovdate();
	    $result[$counter]['isopen'] = $consol->getIsopen();
            $result[$counter]['countryfrom'] = $consol->getCountryfrom()->getName();
	    $result[$counter]['countryto'] = $consol->getCountryto()->getName();
	    $result[$counter]['sender'] = $consol->getSender()->getName();
	    $result[$counter]['receiver'] = $consol->getReceiver()->getName();
	    $result[$counter]['shippingtype'] = $consol->getShippingtype()->getName();
	    $result[$counter]['agency'] = $consol->getAgency()->getName();
	    $counter++;
        }

	$data = array('error'=> 0, 'pagenumber' => $page, 'pagesize' => $defaultsize, 'count' => $numconsol , 'consolidated' => $result);
        $view = $this->view($data);
        return $this->handleView($view);

	
    }
   /**
     * Get one consol
     * @return array
     * @View()
     * @Get("/getconsol/{id}")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getconsolAction($id){

        $consol = $this->getDoctrine()->getRepository("NvCargaBundle:Consolidated")->find($id);
	$translator = $this->get('translator');
	if (!$consol) {
            $data = array('error' => -51 , 'message' => 'No existe el ' . $translator->trans('Consolidado'), 'id' => $id);
	    $view = $this->view($data);
            return $this->handleView($view);
        }
	$result = array();
	
	if ($consol) {
        	$result['id'] = $consol->getId();
		$result['number'] = $consol->getNumber();
		$result['isopen'] = $consol->getIsopen();
        	$result['countryfrom'] = $consol->getCountryfrom()->getName();
		$result['countryto'] = $consol->getCountryto()->getName();
		$result['sender'] = $consol->getSender()->getName();
		$result['receiver'] = $consol->getReceiver()->getName();
		$result['shippingtype'] = $consol->getShippingtype()->getName();
		$result['agency'] = $consol->getAgency()->getName();
		$cguide=0;
		foreach ($consol->getGuides() as $guide) {
			$result['guides'][$cguide]['id'] = $guide->getId();
			$result['guides'][$cguide]['number'] = $guide->getNumber();
			$result['guides'][$cguide]['sender'] = $guide->getSender()->getName() . ' ' . $guide->getSender()->getLastname();
			$result['guides'][$cguide]['receiver'] = $guide->getAddressee()->getName() . ' ' . $guide->getAddressee()->getLastname();
			$result['guides'][$cguide]['date'] = $guide->getCreationdate();
			$result['guides'][$cguide]['realweight'] = $guide->getRealweight();
			if ($guide->getBag()) {
				$result['guides'][$cguide]['bag'] = $guide->getBag()->getNumber();
			} else {
				$result['guides'][$cguide]['bag'] =  'N/A';
			}
			$cguide++;
		}
		$cmove = 0;
		foreach ($consol->getMoves() as $move) {
			$result['moves'][$cmove]['id'] = $move->getId();
			$result['moves'][$cmove]['status'] = $move->getStatus()->getName();
			$result['moves'][$cmove]['movdate'] = $move->getMovdate();
			$result['moves'][$cmove]['comment'] = $move->getComment();
			$result['moves'][$cmove]['percentage'] = $move->getPercentage();
			$cmove++;
		}

	} 
        $data = array('error'=> 0, 'consol' => $result);
        $view = $this->view($data);
        return $this->handleView($view);
       // 	return array('consol' => $result);
    }

    /**
     * Get data for create a consolidated
     * @return array
     * @Get("/new")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(){

        $companies = $this->getDoctrine()->getRepository("NvCargaBundle:Company")->findAll();
	$agencies = $this->getDoctrine()->getRepository("NvCargaBundle:Agency")->findAll();
	$shippingtypes = $this->getDoctrine()->getRepository("NvCargaBundle:Shippingtype")->findAll();

	$ragencies = array();
	$counter = 0;
        foreach ($agencies as $agency) {
	    $ragencies[$counter]['id'] = $agency->getId();
	    $ragencies[$counter]['name'] = $agency->getName();
	    $counter++;
        }

	$rcompanies = array();
	$counter = 0;
        foreach ($companies as $company) {
	    $rcompanies[$counter]['id'] = $company->getId();
	    $rcompanies[$counter]['name'] = $company->getName();
	    $counter++;
        }

	$rshipping = array();
	$counter = 0;
        foreach ($shippingtypes as $shipping) {
	    $rshipping[$counter]['id'] = $shipping->getId();
	    $rshipping[$counter]['name'] = $shipping->getName();
	    $counter++;
        }
	$data = array('companies' => $rcompanies, 'agencies' => $ragencies, 'shippingtypes' => $rshipping );

        $view = $this->view($data);
        return $this->handleView($view);

	
    }
    /**
     * creata a new consolidated
     * @return array
     * @Post("/create")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Consolidated();
	$datapost = $request->request->all();
        
	$senderid = intval($datapost['sender']);
	$receiverid = intval($datapost['receiver']);
	$agencyid = intval($datapost['agency']);
	$shippingtypeid = intval($datapost['shippingtype']);
	
	$error = 0;
	$message = '';
        if (!$senderid) {
		$error = -1;
		$message = $message . 'Debe escoger la compañía remitente. ';
        }
        if (!$receiverid) {
        	$error =  $error-2;
		$message = $message . 'Debe escoger la compañía destinataria. '; 
        }
        if (!$agencyid) {
		$error =  $error-3;
		$message = $message . 'Debe escoger la agencia. '; 
        }
        if (!$shippingtypeid) {
		$error =  $error-4;
		$message = $message . 'Debe escoger el método de envío. ';      
	}  
	if ($error < 0) {
		$data = array('error' => $error, 'message' => $message, 
		'senderid' => $senderid,
		'receiverid' => $receiverid,
		'agencyid' => $agencyid,
		'shippingtypeid' => $shippingtypeid
		 ); 
        	 $view = $this->view($data);
        	 return $this->handleView($view);
	}  
	
	$em = $this->getDoctrine()->getManager();
	$office = $em->getRepository('NvCargaBundle:Office')->find(1);
	
        $sender = $em->getRepository('NvCargaBundle:Company')->find($senderid);
	$receiver = $em->getRepository('NvCargaBundle:Company')->find($receiverid);
	$agency = $em->getRepository('NvCargaBundle:Agency')->find($agencyid);
	$shippingtype = $em->getRepository('NvCargaBundle:Shippingtype')->find($shippingtypeid);

	if (!$sender) {
		$error =  $error-1;
		$message = $message . 'No existe la compañía remitente. ';
	}
	if (!$receiver) {
		$error =  $error-2;
		$message = $message . 'No existe la compañía destinataria. ';
	}
	if (!$agency) {
		$error =  $error-3;
		$message = $message . 'No existe la agencia. ';
	}
	if (!$shippingtype) {
		$error =  $error-4;
		$message = $message . 'No existe el método de envío. ';
	}
	if ($error < 0) {
		$error = $error - 10;
		$data = array('error' => $error, 'message' => $message, 
		'senderid' => $senderid,
		'receiverid' => $receiverid,
		'agencyid' => $agencyid,
		'shippingtypeid' => $shippingtypeid
		 ); 
        	 $view = $this->view($data);
        	 return $this->handleView($view);
	}	
	if ($sender === $receiver) {
		 $data = array('error' => -21, 'message' => 'Remitente y Destinatario no pueden ser iguales. ', 
		 	'senderid' => $senderid,
			'receiverid' => $receiverid,
			'agencyid' => $agencyid,
			'shippingtypeid' => $shippingtypeid);
        	 $view = $this->view($data);
        	 return $this->handleView($view);
	}
	$entity->setSender($sender);
	$entity->setReceiver($receiver);
	$entity->setAgency($agency);
	$entity->setShippingtype($shippingtype);
	$entity->setCountryto($receiver->getCountry());
	$entity->setCountryfrom($sender->getCountry());
	$entity->setOffice($office);
	$goto = $entity->getCountryto();
	$from = $entity->getCountryfrom();
	$entity->open();
	$consol = $em->getRepository('NvCargaBundle:Consolidated')
			->findOneBy(array('isopen' => true,
					'sender' => $sender,
					'receiver' => $receiver,
					'agency' => $agency, 
					'countryto' => $goto,
					'countryfrom' => $from,
					'shippingtype' => $shippingtype));
	if ($consol) {
		$data = array('error' => -41, 'message' => 'Ya tiene uno abierto con esas opciones, debe cerrarlo para crear otro', 
		 	'senderid' => $senderid,
			'receiverid' => $receiverid,
			'agencyid' => $agencyid,
			'shippingtypeid' => $shippingtypeid);
        	$view = $this->view($data);
        	return $this->handleView($view);
	}
	$move = new Moveconsols();
	    $move->setMovdate(new \DateTime());
	    $move->setConsolidated($entity);
	    $status = $em->getRepository('NvCargaBundle:Consolidatedstatus')->findOneByPosition(1);
	    $move->setStatus($status);
	    $comment = 'Creado en la agencia ' . $agency;
	    $move->setComment($comment);
	    $move->setPercentage(0);
	
	    $entity->addMove($move);
	    $em->persist($move);

	    $translator = $this->get('translator');
	    $entity->setNumber('00000');
	    $em->persist($entity);
	    $em->flush();
	    $theid = $entity->getId();
	    $format = $translator->trans('mconsoldate');
	    $userag = $entity->getAgency();
	    $prefix = $userag->getMaincompany()->getPrefixconsol();
	    $mydate = new \DateTime();
	    if ($format != 'mconsoldate') {
		$mid = $mydate->format($format);
	    } else {
		$mid = '';
	    }
	    $number = $prefix . $mid  . '-' . $theid; 
	    $entity->setNumber($number);
           
	    
            $em->persist($entity);
            $em->flush();
	$data = array('error' => $error, 'id'=>$entity->getId(), 'number' => $entity->getNumber(), 'message' => 'Se ha creado');
        $view = $this->view($data);
        return $this->handleView($view);
    }

   /**
     * update a consolidated
     * @return array
     * @Put("/update/{id}")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
	$em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Consolidated')->find($id);
	
	$translator = $this->get('translator');
        if (!$entity) {
            $data = array('error' => -51 , 'message' => 'No existe el ' . $translator->trans('Consolidado'), 'id' => $id);
	    $view = $this->view($data);
            return $this->handleView($view);
        }
	
	if (!$entity->getGuides()->isEmpty()) { 
		$data = array('error' => -61 , 'message' => 'No se puede editar un ' . $translator->trans('Consolidado') . ' con ' .   $translator->trans('Guías'), 'id' => $id);
		$view = $this->view($data);
        	return $this->handleView($view);
	}
	if (!$entity->getIsopen()) { 
		$data = array('error' => -71 , 'message' => 'Para editar el ' . $translator->trans('Consolidado') . ' debe estar abierto.', 'id' => $id);
		$view = $this->view($data);
        	return $this->handleView($view);
	}
	$datapost = $request->request->all();
        
	$senderid = intval($datapost['sender']);
	$receiverid = intval($datapost['receiver']);
	$shippingtypeid = intval($datapost['shippingtype']);
	
	$csender=$entity->getSender()->getId();
	$creceiver=$entity->getReceiver()->getId();
	$cshipping=$entity->getShippingtype()->getId();
	
        $sender = $em->getRepository('NvCargaBundle:Company')->find($senderid);
	$receiver = $em->getRepository('NvCargaBundle:Company')->find($receiverid);
	$shippingtype = $em->getRepository('NvCargaBundle:Shippingtype')->find($shippingtypeid);

	$error = 0;
	$message = '';
	$change = false;
	if (($senderid) && ($senderid != $csender)) {
		if ($sender) {
			$entity->setSender($sender);
			$entity->setCountryfrom($sender->getCountry());
			$change = true;
		} else {
			$error =  $error-1;
			$message = $message . 'No existe la compañía remitente. ';
		}
	}
	if (($receiverid) && ($receiverid != $creceiver)) {
		if ($receiver) {
			$entity->setReceiver($receiver);
			$entity->setCountryto($receiver->getCountry());
			$change = true;
		} else {
			$error =  $error-2;
			$message = $message . 'No existe la compañía destinataria. ';
		}
	}
	if (($shippingtypeid) && ($shippingtypeid != $cshipping)) {
		if ($shippingtype) {
			$entity->setShippingtype($shippingtype);
			$change = true;
		} else {
			$error =  $error-2;
			$message = $message . 'No existe el método de envío. ';
		}
	}
	if ($error < 0) {
		$error = $error - 10;
		$data = array('error' => $error, 'message' => $message, 
		'senderid' => $senderid,
		'receiverid' => $receiverid,
		'shippingtypeid' => $shippingtypeid
		 ); 
        	 $view = $this->view($data);
        	 return $this->handleView($view);
	}	
	if ($entity->getSender() === $entity->getReceiver()) {
		 $data = array('error' => -21, 'message' => 'Remitente y Destinatario no pueden ser iguales. ', 
		 	'senderid' => $senderid,
			'receiverid' => $receiverid,
			'shippingtypeid' => $shippingtypeid);
        	 $view = $this->view($data);
        	 return $this->handleView($view);
	}
	
	$goto = $entity->getCountryto();
	$from = $entity->getCountryfrom();
	
	$consol = $em->getRepository('NvCargaBundle:Consolidated')
			->findOneBy(array('isopen' => true,
					'sender' => $entity->getSender(),
					'receiver' => $entity->getReceiver(),
					'agency' => $entity->getAgency(), 
					'countryto' => $goto,
					'countryfrom' => $from,
					'shippingtype' => $entity->getShippingtype()));
	if (($consol) && ($change)) {
		$data = array('error' => -41, 'message' => 'Ya tiene un tiene  uno abierto con esas opciones, debe cerrarlo para cambiar las opciones de este', 
		 	'senderid' => $senderid,
			'receiverid' => $receiverid,
			'shippingtypeid' => $shippingtypeid);
        	$view = $this->view($data);
        	return $this->handleView($view);
	}
	if ($change) {
		$em->flush();
	}
	$data = array('error' => 0, 'message' => 'Se ha modificado');
        $view = $this->view($data);
        return $this->handleView($view);
    }
    /**
     * list of guides to Consolidated
     * @return array
     * @Get("/guides/{id}")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function guidesAction(Request $request, $id)
    {
	
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NvCargaBundle:Consolidated')->find($id);
	$translator = $this->get('translator');
	if (!$entity) {
            $data = array('error' => -51 , 'message' => 'No existe el ' . $translator->trans('Consolidado'), 'id' => $id);
	    $view = $this->view($data);
            return $this->handleView($view);
        }
	$lastmove = $entity->getMoves()->last();
	if (($lastmove->getStatus()->getPosition() != 1) || !($entity->getIsopen())) {
		$data = array('error' => -61 , 'message' => 'El status no permite agregar.', 'id' => $id);
		$view = $this->view($data);
        	return $this->handleView($view);
	} 
	
	$agency = $entity->getAgency();
	$countryto = $entity->getCountryto();
	$shiptype = $entity->getShippingtype();
	
	$listguides = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
    			->where('g.agency = :ag')
			->andwhere('g.countryto = :ct')
			->andwhere('g.shippingtype = :st')
			->andwhere('g.consol IS NULL')
    			->setParameters(array('ag' => $agency, 
					      'ct' => $countryto, 'st'=>$shiptype ))
    			->orderBy('g.number', 'ASC')
    			->getQuery()
			->getResult();
	$cguide=0;
	$guides = array();
	foreach ($listguides as $guide) {
		$guides[$cguide]['id'] = $guide->getId();
		$guides[$cguide]['number'] = $guide->getNumber();
		$guides[$cguide]['sender'] = $guide->getSender()->getName() . ' ' . $guide->getSender()->getLastname();
		$guides[$cguide]['receiver'] = $guide->getAddressee()->getName() . ' ' . $guide->getAddressee()->getLastname();
		$guides[$cguide]['date'] = $guide->getCreationdate();
		$guides[$cguide]['realweight'] = $guide->getRealweight();
		if ($guide->getBag()) {
			$guides[$cguide]['bag'] = $guide->getBag()->getNumber();
		} else {
			$guides[$cguide]['bag'] =  'N/A';
		}
		$cguide++;
	}
	$data = array('error' => 0, 'guides' => $guides);
	$view = $this->view($data);
        return $this->handleView($view);
		
    }
    /**
     * creata a new consolidated
     * @return array
     * @Post("/addguides/{id}")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addguidesAction(Request $request, $id)
    {
	$datapost = $request->request->all();
	$list = $datapost['guidelist'];

	$guidelist=json_decode($list);
	$total=count($guidelist);
	$em = $this->getDoctrine()->getManager();
	$consol = $em->getRepository('NvCargaBundle:Consolidated')->find($id);
	$translator = $this->get('translator');
	if (!$consol) {
                $data = array('error' => -5, 'message' => 'No existe el  ' . $translator->trans('Consolidado'));
		$view = $this->view($data);
        	return $this->handleView($view);
        }
	$lastmove = $consol->getMoves()->last();
	if (($lastmove->getStatus()->getPosition() != 1) || !($consol->getIsopen())) {
		$data = array('error' => -9, 'message' => 'El status no permite agregar ');
		$view = $this->view($data);
        	return $this->handleView($view);
	}
	// $guides = $em->getRepository('NvCargaBundle:Guide')->findById($guidelist);
	$guides = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
    			->where('g.id IN (:ids)')
			->andwhere('g.agency = :ag')
			->andwhere('g.countryto = :ct')
			->andwhere('g.shippingtype = :st')
			->andwhere('g.consol IS NULL')
    			->setParameters(array('ag' => $consol->getAgency(), 
					      'ct' => $consol->getCountryto(), 
					 	'st'=>$consol->getShippingtype(),
						'ids'=>$guidelist))
    			->orderBy('g.number', 'ASC')
    			->getQuery()
			->getResult();
	$consol = $em->getRepository('NvCargaBundle:Consolidated')->find($id);
	
	if (count($guides) != $total ) {
		$data = array('error' => -9, 'message' => 'No se puede incluir.');
		$view = $this->view($data);
        	return $this->handleView($view);
		
	}

	foreach ($guides as $guide) {
		$guide->setConsol($consol);
		$consol->addGuide($guide);
		$moveguide = new Moveguides($this->get('translator'), $this->get('mailer'), $this->getParameter('mailer_user'), $this->getParameter('mailer_username'));
		$moveguide->setMovdate(new \DateTime());
		$moveguide->setGuide($guide);
		$stamov = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName('Consolidada');
		$moveguide->setStatus($stamov);
		$company = $em->getRepository('NvCargaBundle:Localcompany')->findOneByName('Empresa');
		$moveguide->setCompany($company);
		$moveguide->setTrack(' ');
		$moveguide->setPercentage(10);
		$translator = $this->get('translator');
		$moveguide->setComment($translator->trans('Guía') . ' en ' .  $translator->trans('Consolidado'));
		$guide->addMove($moveguide); 
		$moveguide->Sendemail();
		$em->persist($moveguide);
	}
	$bag = $guides[0]->getBag();
	if ($bag) {
		$bag->setStatus("CONSOLIDADA");
	}
	$em->flush();	
	$data = array('error' => 0, 'message' => 'Se agregaron las ' . $translator->trans('Guías') . 'al ' . $translator->trans('Consolidado'), 'guides' => $guidelist);
	$view = $this->view($data);
        return $this->handleView($view);
	
    }
}
