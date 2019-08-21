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

use NvCarga\Bundle\Entity\Guide;
use NvCarga\Bundle\Entity\Moveguides;


/**
 * Guide controller for API.
 * @Prefix("/guide")
 */
class GuideAPIController extends FOSRestController
{
   /**
     * Get one consol
     * @return array
     * @View()
     * @Get("/getguide/{id}")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getguideAction($id){

        $entity = $this->getDoctrine()->getRepository("NvCargaBundle:Guide")->find($id);
	$translator = $this->get('translator');
	if (!$entity) {
            $data = array('error' => -51, 'message' => 'No existe ' . $translator->trans('Guía'),   'id' => $id);
	    $view = $this->view($data);
            return $this->handleView($view);
        }
	$result = array();

       	$result['id'] = $entity->getId();
	$result['number'] = $entity->getNumber();
	$result['sender'] = $entity->getSender()->getName() . ' ' . $entity->getSender()->getLastname();
	$result['receiver'] = $entity->getAddressee()->getName() . ' ' . $entity->getAddressee()->getLastname();
	$result['date'] = $entity->getCreationdate();
	$result['realweight'] = $entity->getRealweight();
	$result['tariff'] = $entity->getTariff()->getName();
	$result['shippingtype'] = $entity->getShippingtype()->getName();
	$result['countryfrom'] = $entity->getCountryfrom()->getName();
	$result['countryto'] = $entity->getCountryto()->getName();
	$result['agency'] = $entity->getAgency()->getName();
	$result['totalpaid'] = $entity->getTotalpaid();
	$result['declared'] = $entity->getDeclared();
	if ($entity->getBag()) {
		$result['bag'] = $entity->getBag()->getNumber();
	} else {
		$result['bag'] =  'N/A';
	}
	$crec=0;
	foreach ($entity->getReceipts() as $receipt) {
		$result['receipts'][$crec]['id'] = $receipt->getId();
		$result['receipts'][$crec]['number'] = $receipt->getNumber();
		$result['receipts'][$crec]['length'] = $receipt->getLength();
		$result['receipts'][$crec]['width'] = $receipt->getWidth();
		$result['receipts'][$crec]['height'] = $receipt->getHeight();
		$result['receipts'][$crec]['weight'] = $receipt->getWeight();
		$crec++;
	}
	$cmove = 0;
	foreach ($entity->getMoves() as $move) {
		$result['moves'][$cmove]['id'] = $move->getId();
		$result['moves'][$cmove]['status'] = $move->getStatus()->getName();
		$result['moves'][$cmove]['movdate'] = $move->getMovdate();
		$result['moves'][$cmove]['comment'] = $move->getComment();
		$result['moves'][$cmove]['percentage'] = $move->getPercentage();
		$cmove++;
	}	
        $data = array('error' => 0, 'guide' => $result);
        $view = $this->view($data);
        return $this->handleView($view);  
    }
}
