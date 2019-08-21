<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Region;
use NvCarga\Bundle\Form\RegionType;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Region controller.
 *
 * @Route("/region")
 */
class RegionController extends Controller
{

    /**
     * Lists all Region entities.
     *
     * @Route("/index", name="region")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_STATE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_STATE')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user= $this->getUser();
        $maincompany = $user->getMaincompany();
        $countries = $maincompany->getCountries()->toArray();
        $entities = $em->getRepository('NvCargaBundle:Region')->findBy(['maincompany'=>$maincompany,'country'=>$countries]);
        $listall = $maincompany->getCountries();
        $countries = array();
        $count = 0;
        foreach ($listall as $country) {
            $countries[$count]['id'] = $country->getName();
            $countries[$count]['name'] = $country->getName();
            $count++;
        }
        return array(
            'entities' => $entities,
            'countries' => $countries,
        );
    }
    /**
     * Creates a new Region entity.
     *
     * @Route("/create", name="region_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Region:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_STATE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Region();
        $form = $this->createCreateForm($entity);
        $user= $this->getUser();
        $maincompany = $user->getMaincompany();
        
        $form->handleRequest($request);

        if ($form->isValid()) {
            $country =  $form['country']->getData();
            $em = $this->getDoctrine()->getManager();
            $region = $em->getRepository('NvCargaBundle:Region')->findOneBy(array('maincompany'=>$maincompany, 'country'=>$entity->getCountry(), 'name'=>$entity->getName()));
            if ($region) {
                throw $this->createNotFoundException("Ya existe una región con el nombre ".  $entity->getName() . ' en ' . $entity->getCountry());
            }
            $entity->setMaincompany($maincompany);
            if (count($entity->getRegionCities()) == 0) {
                throw $this->createNotFoundException('Debe seleccionar al menos una ciudad.. ');
            }
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('region_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Region entity.
     *
     * @param Region $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Region $entity)
    {
        $form = $this->createForm(new RegionType($this->getDoctrine()->getManager(), $this->getUser()), $entity, array(
            'action' => $this->generateUrl('region_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Region entity.
     *
     * @Route("/new", name="region_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_STATE') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Region();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Region entity.
     *
     * @Route("/show/{id}", name="region_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_STATE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_STATE')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user= $this->getUser();
        $maincompany = $user->getMaincompany();
        
        $entity = $em->getRepository('NvCargaBundle:Region')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe esa Región');
        }
        
        return array(
            'entity'      => $entity,
        );
    }

   /**
     * Displays a form to edit an existing Region entity.
     *
     * @Route("/{id}/edit", name="region_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_STATE') or has_role('ROLE_ADMIN')")
     */
     
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Region')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe esa Región');
        }
        
        $pos1 = strpos($entity->getName(), 'Todas');
        $pos2 = strpos($entity->getName(), $entity->getCountry()->getName());
        // exit(\Doctrine\Common\Util\Debug::dump([$pos1, $pos2]));
        if (($pos1 !== false) && ($pos2 !== false)) {
            throw $this->createNotFoundException('La región no es EDITABLE');
        }
        $editForm = $this->createEditForm($entity);

        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
        );
    }
    /**
    * Creates a form to edit a Region entity.
    *
    * @param Region $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    
    private function createEditForm(Region $entity)
    {
        $country = $entity->getCountry();
        $cities = $entity->getRegionCities();
        $em = $this->getDoctrine()->getManager();
        $states = $em->getRepository('NvCargaBundle:State')->findByCountry($country, array('name' => 'asc'));
        $cities_all = $em->getRepository('NvCargaBundle:City')->findByState($states, array('name' => 'asc'));
        $form = $this->get('form.factory')->createNamedBuilder('region_type', 'form', $entity, 
                array('action' => $this->generateUrl('region_update', array('id' => $entity->getId())),
           		 'method' => 'PUT'))
                ->add('name', 'text',  array('label'=> 'Nombre '))
                ->add('country', 'entity', array('label'=> 'País ', 'data' => $country, 
                    'class' => 'NvCargaBundle:Country', 'read_only'=>TRUE))
                ->add('state', 'entity', array('label'=> 'Estados ', 
            					'class' => 'NvCargaBundle:State',
            					'choices' => $states, 
                            'multiple' => true, 
                            'mapped' => false))
                ->add('region_cities', 'entity', array('label'=> 'Ciudades actalues', 
                                'empty_value' => '-- Seleccione primero los Estados --',
            					'class' => 'NvCargaBundle:City',
            					'choices' => $cities,
                                'multiple' => true, ))
                ->add('new_cities', 'entity', array('label'=> 'Agregar Ciudades ', 
                                'empty_value' => '-- Seleccione primero los Estados --',
            					'class' => 'NvCargaBundle:City',
            					'choices' => $cities_all,
                                'multiple' => true, 'mapped'=> false, 'required'=>false))
						// 'expanded' => true))
                ->getForm();
        
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Region entity.
     *
     * @Route("/update/{id}", name="region_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Region:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_STATE') or has_role('ROLE_ADMIN')")
     */
    
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Region')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe esa Región');
        }
        $namecur = $entity->getName();
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $idem = $em->getRepository('NvCargaBundle:Region')->findOneBy(array('country'=>$entity->getCountry(), 'name'=>$entity->getName(),'maincompany'=>$maincompany));
            if (($idem) && ($entity->getName() != $namecur)) {
                throw $this->createNotFoundException("Ya existe una región con el nombre ".  $entity->getName() . ' en ' . $entity->getCountry());
            }
            $newcities = $editForm['new_cities']->getData();
            // exit(\Doctrine\Common\Util\Debug::dump($newcities));
            $current = array();
            foreach ( $entity->getRegionCities() as $city) {
                $current[] = $city;
            }
            foreach ($newcities as $city) {
                if (!in_array($city,$current)) {
                    $entity->addRegionCity($city);
                }
            }
            $em->flush();
            return $this->redirect($this->generateUrl('region_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
        );
    }
    /**
     * @Route("/regions", name="select_regions")
     */
    public function regionsAction(Request $request) {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        // Get the country ID
        $id = $request->query->get('country_id');
        
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $country = $em->getRepository('NvCargaBundle:Country')->find($id);
        
        $result = array();
        
        // Return a list of  regions
        $regions = $em->getRepository('NvCargaBundle:Region')->findBy(['maincompany'=>$maincompany, 'country'=>$country], array('name' => 'asc'));
        foreach ($regions as $region) {
            $result[$region->getName()] = $region->getId();
        }
        return new JsonResponse($result);
    }
}
