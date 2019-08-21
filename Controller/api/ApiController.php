<?php

namespace NvCarga\Bundle\Controller\api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;


class ApiController extends FOSRestController
{
    /**
     * Get all the guides
     * @return array
     * @View()
     * @Get("/countries")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getCountriesAction(){

        $countries = $this->getDoctrine()->getRepository("NvCargaBundle:Country")->findAll();
      
       	return array('countries' => $countries);
    }
/**
    * Get all the guides
     * @return array
     * @View()
     * @Get("/country/{id}",)
     */
    public function getCountryIdAction($id){

        $country = $this->getDoctrine()->getRepository("NvCargaBundle:Country")->find($id);
	
        return array('country' => $country->getName());
    }
}
