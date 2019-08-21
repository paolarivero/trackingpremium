<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\City;
use NvCarga\Bundle\Form\CityType;

/**
 * City controller.
 *
 * @Route("/city")
 */
class CityController extends Controller
{

    /**
     * Lists all City entities.
     *
     * @Route("/index", name="city")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CITY') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_CITY')")
     */
    public function indexAction()
    {
    
        $em = $this->getDoctrine()->getManager();
    /*
        $entities = $em->getRepository('NvCargaBundle:City')->findAll();

        return array(
            'entities' => $entities,
        );
    */
        $listall = $this->getUser()->getMaincompany()->getCountries();
        $countries = array();
        $count = 0;
        foreach ($listall as $country) {
            $countries[$count]['id'] = $country->getId();
            $countries[$count]['name'] = $country->getName();
            $count++;
        }
        return  $this->render('NvCargaBundle:City:index.html.twig',array('countries' => $countries));  

    }
    /**
     * Creates a new City entity.
     *
     * @Route("/create", name="city_create")
     * @Method("POST")
     * @Template("NvCargaBundle:City:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_CITY') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new City();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
	    $city = $em->getRepository('NvCargaBundle:City')->findOneBy(array('state' => $entity->getState(), 'name' => $entity->getName()));
	    if ($city) {
		throw $this->createNotFoundException('Existe una ciudad con ese nombre en ese estado..');
	    }
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('city_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a City entity.
     *
     * @param City $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(City $entity)
    {
        $form = $this->createForm(new CityType($this->getDoctrine()->getManager()), $entity, array(
            'action' => $this->generateUrl('city_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Creates a form to create a City entity.
     *
     * @param City $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSpecialForm(City $entity)
    {
        $form = $this->createForm(new CityType($this->getDoctrine()->getManager()), $entity, array());

        $form->add('save', 'button', array('label' => 'Guardar'));

        return $form;
    }
    /**
     * Displays a form to create a new City entity.
     *
     * @Route("/new", name="city_new")
     * @Method("GET")
     * @Template("NvCargaBundle:City:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_CITY') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new City();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

   /**
     * Displays a form to create a new City entity.
     *
     * @Route("/new_special", name="city_newspecial")
     * @Template("NvCargaBundle:City:new_special.html.twig")
     * @Security("has_role('ROLE_ADMIN_CITY') or has_role('ROLE_ADMIN')")
     */
    public function new_specialAction()
    {
        $entity = new City();
        $em = $this->getDoctrine()->getManager();
        $form   = $this->createSpecialForm($entity);
        
        
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Finds and displays a City entity.
     *
     * @Route("/{id}/show", name="city_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CITY') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_CITY')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:City')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find City entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing City entity.
     *
     * @Route("/{id}/edit", name="city_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CITY') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:City')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find City entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a City entity.
    *
    * @param City $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(City $entity)
    {
        $form = $this->createForm(new CityType($this->getDoctrine()->getManager()), $entity, array(
            'action' => $this->generateUrl('city_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing City entity.
     *
     * @Route("/{id}/update", name="city_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:City:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_CITY') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:City')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find City entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('city_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a City entity.
     *
     * @Route("/{id}/delete", name="city_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_CITY') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:City')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find City entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('city'));
    }

    /**
     * Creates a form to delete a City entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('city_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
    private function createSearchForm()
    {
        $formBuilder = $this->get('form.factory')->createNamedBuilder('form_search', 'form', array())
        ->add('name', 'text', array('label' => 'Nombre ', 'required' => false))
        ->add('search', 'button', array('label' => 'Buscar'));
        $form = $formBuilder->getForm();
        return $form;
    }

    /**
     * show a list of Cities 
     * @Route("/list",  name="city_list")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function listAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $name = $request->query->get('city_name');

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $countries = $maincompany->getCountries()->toArray();
        
        $states = $em->getRepository('NvCargaBundle:State')->findByCountry($countries);
        
        $entities = $em->getRepository('NvCargaBundle:City')->createQueryBuilder('o')
            ->where('o.name LIKE :name')
            //->setParameter('name', '%'.$name.'%')
            ->andWhere('o.state IN (:states)')
            ->andWhere('o.active =:active')
            ->setParameters(['name'=>$name.'%','states'=>$states, 'active'=>true])
            ->setFirstResult(0)
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
                
        $result=array();
        $counter = 0;
        foreach ($entities as $entity) {
            $result[$counter]['cityid'] = $entity->getId();
            $result[$counter]['cityname'] = $entity->getName();
            $result[$counter]['state'] = $entity->getState()->getName();
            $result[$counter]['country'] = $entity->getState()->getCountry()->getName();
            $counter++;
        }
        return new JsonResponse($result); 
    }
    /**
     * show a list of all Cities 
     * @Route("/listall",  name="city_listall")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function listallAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('NvCargaBundle:City')->findByActive(true);
        $result=array();
        $counter = 0;
        foreach ($entities as $entity) {
            $result[$counter]['cityid'] = $entity->getId();
            $result[$counter]['cityname'] = $entity->getName();
            $result[$counter]['state'] = $entity->getState()->getName();
            $result[$counter]['country'] = $entity->getState()->getCountry()->getName();
            $counter++;
        }
        // sleep(5);
        return new JsonResponse($result); 
    }
    /**
     * show a list of all Cities 
     * @Route("/listpobox",  name="city_listpobox")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function listpoboxAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $name = $request->query->get('city_name');
        $len = strlen($name);

        $em = $this->getDoctrine()->getManager();
        $rs = $em->getRepository('NvCargaBundle:Tariff')->createQueryBuilder('t')
                ->select('DISTINCT r.id')
                ->leftjoin('t.region', 'r')
                ->where('r.id = t.region')
                ->getQuery()
                ->getResult();

        // $regions = $em->getRepository('NvCargaBundle:Region')->findBy(array('id' => $rs));

    
        $regions = $em->getRepository('NvCargaBundle:Region')->createQueryBuilder('r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids',$rs)
            ->getQuery()
            ->getResult();

        $entities = array();
        foreach ($regions as $region) {
            // exit(\Doctrine\Common\Util\Debug::dump($region->getName())); 
            $cities = $region->getRegionCities();
            foreach ($cities as $city) {
                if ((!in_array($city, $entities)) && (strncasecmp($city->getName(), $name, $len) == 0) && ($city->getActive())){
                    $entities[] = $city;
                }
            }
        }
        /*
            $entities = $em->getRepository('NvCargaBundle:City')->createQueryBuilder('c')
                ->select('c')
                ->where('c.state IN (:ids)')
                        ->setParameter('ids',$states)
                ->getQuery()
                ->getResult();

        */
        $result=array();
        $counter = 0;
        foreach ($entities as $entity) {
            $result[$counter]['cityid'] = $entity->getId();
            $result[$counter]['cityname'] = $entity->getName();
            $result[$counter]['state'] = $entity->getState()->getName();
            $result[$counter]['country'] = $entity->getState()->getCountry()->getName();
            $counter++;
        }
        // exit(\Doctrine\Common\Util\Debug::dump($result));
        return new JsonResponse($result); 
    }
    /**
     * Add a new City entity.
     * @Route("/add", name="city_add")
     * @Security("has_role('ROLE_ADMIN_CITY') or has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $entity = new City();
        $em = $this->getDoctrine()->getManager();
        $name = $request->query->get('city_name');
        $state = $request->query->get('city_state'); 
        $error=false;
        if (!$name) {
            $result='Error: Debe seleccionar un nombre de ciudad';
            return new JsonResponse($result);
        }
        if (!$state) {
            $result='Error:Debe seleccionar un estado/provincia para la ciudad';
            return new JsonResponse($result);
        }
        $thestate = $em->getRepository('NvCargaBundle:State')->find($state);
        if (!$thestate) {
            $result='Error: El estado/provincia seleccionado no existe';
                return new JsonResponse($result);
        }
        $city = $em->getRepository('NvCargaBundle:City')->findOneBy(array('state' => $state, 'name' => $name));
        if ($city) {
            $result='Error: Existe una ciudad con ese nombre en ese estado';
                return new JsonResponse($result);
        }
        $entity->setName($name);
        $entity->setState($thestate);
        $entity->setActive(false);
        $em->persist($entity);
        $em->flush();
        $result='La ciudad ha sido creada satisfactoriamente';
        return new JsonResponse($result);        
    }
    /**
     * @Route("/cities", name="select_cities")
     */
    public function citiesAction(Request $request) {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        // Get the country ID
        $list = $request->query->get('statelist');
        $statelist=json_decode($list);
        $result = array();
        // Return a list of states
        $repo = $this->getDoctrine()->getManager()->getRepository('NvCargaBundle:City');
        $cities = $repo->findByState($statelist, array('name' => 'asc'));
        // $result[count($cities)] = 0;
        foreach ($cities as $city) {
            if ($city->getActive()) {
                $result[$city->getName()] = $city->getId();
            }
        }
        return new JsonResponse($result);
    }
    
    /**
     * show a list of Cities 
     * @Route("/listcountry",  name="city_listcountry")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function listcountryAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $name = $request->query->get('city_name');
        $list = $request->query->get('countrylist');
        $countrylist = json_decode($list);

        $em = $this->getDoctrine()->getManager();
        $states = $em->getRepository('NvCargaBundle:State')->findByCountry($countrylist);
        
        $entities = $em->getRepository('NvCargaBundle:City')->createQueryBuilder('o')
   			->where('o.name LIKE :name')
            //->setParameter('name', '%'.$name.'%')
            ->andWhere('o.state IN (:states)')
            ->andwhere('o.active =:active')
            ->setParameters(['name'=>$name.'%','states'=>$states, 'active'=>true])
            ->setFirstResult(0)
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
        $result=array();
        $counter = 0;
        foreach ($entities as $entity) {
            $result[$counter]['cityid'] = $entity->getId();
            $result[$counter]['cityname'] = $entity->getName();
            $result[$counter]['state'] = $entity->getState()->getName();
            $result[$counter]['country'] = $entity->getState()->getCountry()->getName();
            $counter++;
        }
	
        return new JsonResponse($result); 
    }
    /**
    * @Route("/cityfind", name="city_find")
    * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
    */
    public function cityfindAction(Request $request)
    {
        $key = $request->query->get('q'); 
        
        // Find rows matching with keyword $key..
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $countries = $maincompany->getCountries()->toArray();
        $states = $em->getRepository('NvCargaBundle:State')->findByCountry($countries);
        
        $entities = $em->getRepository('NvCargaBundle:City')->createQueryBuilder('o')
            ->where('o.name LIKE :name')
            //->setParameter('name', '%'.$name.'%')
            ->andWhere('o.state IN (:states)')
            ->andwhere('o.active =:active')
            ->setParameters(['name'=>$key.'%','states'=>$states, 'active'=>true])
            ->setFirstResult(0)
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();

        // customize your output as per Select2 requirement..
        $result=[];
        foreach ($entities as $entity) {
            $result[] = array('id'=>$entity->getId(), 'text'=>$entity->getName() . ' (' .
            $entity->getState()->getName() . ', ' . $entity->getState()->getCountry()->getName() . ')' );
        }
        return new JsonResponse($result);
    }

    /**
    * @Route("/cityfindbycountry", name="city_findbycountry")
    * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
    */
    public function cityfindbycountryAction(Request $request)
    {
        $key = $request->query->get('q'); 
        $list = $request->query->get('countries'); 
        $countries = json_decode($list);
        
        // Find rows matching with keyword $key..
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        // $countries = $maincompany->getCountries()->toArray();
        $states = $em->getRepository('NvCargaBundle:State')->findByCountry($countries);
        
        $entities = $em->getRepository('NvCargaBundle:City')->createQueryBuilder('o')
            ->where('o.name LIKE :name')
            //->setParameter('name', '%'.$name.'%')
            ->andWhere('o.state IN (:states)')
            ->andwhere('o.active =:active')
            ->setParameters(['name'=>$key.'%','states'=>$states, 'active'=>true])
            ->setFirstResult(0)
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();

        // customize your output as per Select2 requirement..
        $result=[];
        foreach ($entities as $entity) {
            $result[] = array('id'=>$entity->getId(), 'text'=>$entity->getName() . ' (' .
            $entity->getState()->getName() . ', ' . $entity->getState()->getCountry()->getName() . ')' );
        }
        return new JsonResponse($result);
    }
    /**
    * @Route("/findcity", name="city_findcity")
    * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
    */
    public function findcityAction(Request $request)
    {
        $id= $request->query->get('city_id'); 
        
        // Find rows matching with keyword $key..
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NvCargaBundle:City')->find($id);
    
        // customize your output as per Select2 requirement..
        if ($entity) {
            $result =array('id'=>$entity->getId(), 'text'=>$entity->getName() . ' (' .
            $entity->getState()->getName() . ', ' . $entity->getState()->getCountry()->getName() . ')' );
        } else {
            $result= null;
        }
        return new JsonResponse($result);
    }
    /**
     * @Route("/paginate", name="city_paginate")
     */
    public function paginateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
    
        $length = $request->get('length');
        $length = $length && ($length!=-1)?$length:0;

        $start = $request->get('start');
        $start = $length?($start && ($start!=-1)?$start:0)/$length:0;

        $order = $request->get('order');
        $search = $request->get('search');
        $name = @$search['value'];
        
        $myorder = @$order[0]; 
        $dir = @$myorder['dir'];
        
        $country = $request->get('country');
        $maincompany = $this->getUser()->getMaincompany();
        
        $allstates = $em->getRepository('NvCargaBundle:State')->findByCountry($maincompany->getCountries()->toArray());
        
        $thequery = $em->getRepository('NvCargaBundle:City')->createQueryBuilder('o');
        
        $parameters = array();
        
        
        
        if ($country > 0) {
            $allstates = $em->getRepository('NvCargaBundle:State')->findByCountry($country);
        } 
        $parameters['states'] = $allstates;
        $query = 'o.state IN (:states)';
        $parameters['active'] = true;
        $query = $query . ' AND o.active =:active';
        
        if ($name) {
            $parameters['name'] = $name.'%';
            $query = $query . ' AND o.name LIKE :name';
        }
    
        $complete = $em->getRepository('NvCargaBundle:City')->createQueryBuilder('o')
                ->select('COUNT(o.id)')
                ->where('o.state IN (:states)')
                ->andWhere('o.active =:active')
                ->setParameters(['states'=>$allstates, 'active'=>true])
                ->getQuery()
                ->getSingleResult();
        
        $total = $em->getRepository('NvCargaBundle:City')->createQueryBuilder('o')
                ->select('COUNT(o.id)')
                ->where($query)
                ->setParameters($parameters)
                ->getQuery()
                ->getSingleResult();
                
        $thequery->where($query)->setParameters($parameters);
        
        if ($dir == 'asc') {
            $thequery->orderBy('o.name','ASC');
        } else {
            $thequery->orderBy('o.name','DESC');
        }
        
        if ($length) {
            $thequery->setMaxResults($length)->setFirstResult($start*$length);
        }
        
        $cities=$thequery->getQuery()->getResult();
        
        
        $output = array('data' => array(),
            'recordsFiltered' => $total,
            'recordsTotal' => $complete,
           // 'totalstates' => count($allstates),
        );
        /*
        $cities = $em->getRepository('NvCargaBundle:City')->createQueryBuilder('o')
                ->setMaxResults(50)
                ->setFirstResult(0)
                ->getQuery()
                ->getResult();
        
        for ($ii =0; $ii<5; $ii++) {
            $city = $cities[$ii];
        */
        foreach ($cities as $city) {
            $output['data'][] = [
                'name' => $city->getName(),
                'state' => $city->getState()->getName(),
                'country' => $city->getState()->getCountry()->getName(),
                //'nrocus' => count($city->getCustomers()),
            ];
        }
        return new JsonResponse($output);
       //return new Response(json_encode($output), 200, ['Content-Type' => 'application/json']);
    }
}
