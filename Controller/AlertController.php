<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Alert;
use NvCarga\Bundle\Entity\Baddress;
use NvCarga\Bundle\Form\AlertType;

/**
 * Alert controller.
 *
 * @Route("/alert")
 */
class AlertController extends Controller
{
    /**
     * Creates a form to search Alert
     *
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm($track)
    {
        $formBuilder = $this->get('form.factory')->createNamedBuilder('alert_search', 'form',  array())
            ->add('tracking', 'text', array('label' => 'Tracking','data'=>$track))
            ->add('search', 'button', array('label' => 'Buscar'))
                ->getForm()
            ;
        return $formBuilder;
    }

    /**
     * Lists all Alert entities.
     *
     * @Route("/", name="alert")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();
        $maincompany = $user->getMaincompany();

        if ($agency->getType() == "MASTER") {
            $entities = $em->getRepository('NvCargaBundle:Alert')->findByMaincompany($maincompany);
            $agencies = $em->getRepository('NvCargaBundle:Agency')->findByMaincompany($maincompany);
        } else {
            $warehouse = $agency->getWarehouse();
            $poboxs =  $em->getRepository('NvCargaBundle:Pobox')->findByWarehouse($warehouse);
            $entities = $em->getRepository('NvCargaBundle:Alert')->findByPobox($poboxs);
            $agencies = null;
        }
        $images = array();
        foreach ($entities as $entity) {
            if ($entity->getImageSize() > 0) {
                $image = base64_encode(stream_get_contents($entity->getImageData()));
            } else {
                $image = null;
            }
            $images[]=$image;
        }
        $form = $this->createSearchForm(null);
        return array(
            'entities' => $entities,
            'images' => $images,
            'form'   => $form->createView(),
            'agencies' => $agencies,
        );
    }
    /**
     * Lists all Alert entities for specific pobox.
     *
     * @Route("/{id}/list", name="alert_list")
     * @Method("GET")
     * @Template()
     * @Security("is_granted('IS_AUTHENTICATED_FULLY') or has_role('ROLE_ADMIN')")
     */
    public function listAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $pobox = $user->getPobox();
        $poboxin = $em->getRepository('NvCargaBundle:Pobox')->find($id);
        if ($pobox != $poboxin) {
            throw $this->createNotFoundException('El casillero seleccionado no está a su nombre');
        }
        
        $entities = $em->getRepository('NvCargaBundle:Alert')->findByPobox($pobox);

        return array(
                'entities' => $entities,
        );
    }
    /**
     * Creates a new Alert entity.
     *
     * @Route("/create", name="alert_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Alert:new.html.twig")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function createAction(Request $request)
    {
        $entity = new Alert();
        $user = $this->getUser();
        $pobox = $user->getPobox();
        if (!$pobox) {
            throw $this->createNotFoundException('Para crear una alerta de paquete debe tener un casillero');
        }
        $maincompany = $user->getMaincompany();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $countalert =  $maincompany->getCountalerts();
        $plan=$maincompany->getPlan();                    
        if (($plan->getAlerts()) && ($countalert >= $plan->getMaxalerts())) {
            $message = 'Ha llegado al número máximo de ALERTAS permitidas. Para crear más el administrador debe actualizar su plan a uno superior.';
            $flashBag->add('notice',$message);
            goto next;
            // throw $this->createNotFoundException('La empresa ha alcanzado el número MÁXIMO de ALERTAS permitidas.');
        }
        $countalert++;
        $maincompany->setCountalerts($countalert);
        $entity->setPobox($pobox);
        $em = $this->getDoctrine()->getManager();
        /*
        $rs = $em->getRepository('NvCargaBundle:Tariff')->createQueryBuilder('t')
                    ->select('DISTINCT r.id')
                    ->leftjoin('t.region', 'r')
                    ->where('r.id = t.region')
                    //->andWhere('t.maincompany = :maincompany')
                    //->setParameters(array('maincompany' => $maincompany))
                    ->getQuery()
                    ->getResult();
                    
        // $regions = $em->getRepository('NvCargaBundle:Region')->findBy(array('id' => $rs));

        
        $regions = $em->getRepository('NvCargaBundle:Region')->createQueryBuilder('r')
                ->where('r.id IN (:ids)')
                ->setParameter('ids', $rs)
                ->getQuery()
                ->getResult();
        
        $allcities = array();
        foreach ($regions as $region) {
            // exit(\Doctrine\Common\Util\Debug::dump($region->getName())); 
            $cities = $region->getRegionCities();
            foreach ($cities as $city) {
                if (!in_array($city, $allcities)) {
                    $allcities[] = $city;
                }
            }
        }
        $choices = array();
        $numchoices=0;
        $myaddress = $entity->getPobox()->getCustomer()->getBaddress();
        foreach ($myaddress as $address) {
            $mycity = $address->getCity();
            if (in_array($mycity,$allcities)) {
                $choices[] = $address;
                $numchoices++;
            }
        }
        $default = $entity->getPobox()->getCustomer()->getAdrdefault();
        if (!in_array($default->getCity(),$allcities))  {
            if (count($choices) > 0) {
                $default = $choices[0]; 
            } else {
                $default = new Baddress();
            }
        }
        */
        $customer = $pobox->getCustomer();
        $choices = $customer->getBaddress()->toArray();
        $default = $customer->getAdrdefault();
        $entity->setBaddress($default);
        
        $numchoices = count($choices);
        $form   = $this->createCreateForm($entity, $choices);
        $form->handleRequest($request);

        if ($form->isValid()) {
            // exit(\Doctrine\Common\Util\Debug::dump($entity->getBaddress())); 
            $entity->setCreationdate(new \DateTime());
            $entity->setArrivedate(new \DateTime($entity->getArrivedate()));
            // $entity->setPobox($pobox);
            $entity->setIsshowed(true);
            $entity->upload(); 
            $entity->setMaincompany($maincompany);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('alert_show', array('id' => $entity->getId())));
        }

        next:
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'pobox' => $pobox,
            'numchoices' => $numchoices,
        );
    }

    /**
     * Creates a form to create a Alert entity.
     *
     * @param Alert $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Alert $entity, $choices)
    {
        $form = $this->createForm(new AlertType($this->getDoctrine()->getManager()), $entity, array(
            'action' => $this->generateUrl('alert_create'),
            'method' => 'POST',
        ));
        $em = $this->getDoctrine()->getManager();
        $carriers = $em->getRepository('NvCargaBundle:Carrier')->findBy(['maincompany'=>$this->getUser()->getMaincompany(), 'active'=>true]);
        $form->add('carrier', 'entity', array('label'=>'Carrier ', 'required' => true, 
            'class'=>'NvCargaBundle:Carrier', 'choices'=>$carriers));
            
        $form->add('baddress', 'entity', array('label' => false,
                    'class' => 'NvCargaBundle:Baddress',
                    'data_class' => 'NvCarga\Bundle\Entity\Baddress',
                    'choices' => $choices,
                    'choice_label' => 'completedir',
                    'multiple' => false, 
                    'expanded' => true,
                    )); // 'attr'=>array('class'=>'icheck')));
        $form->add('submit', 'submit', array('label' => 'Crear'));
        return $form;
    }

    /**
     * Displays a form to create a new Alert entity.
     *
     * @Route("/new", name="alert_new")
     * @Method("GET")
     * @Template()
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function newAction()
    {
        $entity = new Alert();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        
        $pobox = $user->getPobox();
        if (!$pobox) {
            throw $this->createNotFoundException('Para crear una alerta de paquete debe tener un casillero');
        }
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $countalert =  $maincompany->getCountalerts();
        $plan=$maincompany->getPlan();                    
        if (($plan->getAlerts()) && ($countalert >= $plan->getMaxalerts())) {
            $message = 'Ha llegado al número máximo de ALERTAS permitidas. Para crear más el administrador debe actualizar su plan a uno superior.';
            $flashBag->add('notice',$message);
            // throw $this->createNotFoundException('La empresa ha alcanzado el número MÁXIMO de ALERTAS permitidas.');
        }
        
        
        $entity->setPobox($pobox);
        // $entity->setBaddress($pobox->getCustomer()->getAdrdefault());

        $em = $this->getDoctrine()->getManager();
        /*
        $rs = $em->getRepository('NvCargaBundle:Tariff')->createQueryBuilder('t')
                ->select('DISTINCT r.id')
                ->leftjoin('t.region', 'r')
                ->where('r.id = t.region')
                // ->andWhere('t.maincompany = :maincompany')
                // ->setParameters(array('maincompany' => $maincompany))
                ->getQuery()
                ->getResult();
        // $regions = $em->getRepository('NvCargaBundle:Region')->findBy(array('id' => $rs));
        $regions = $em->getRepository('NvCargaBundle:Region')->createQueryBuilder('r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids',$rs)
            ->getQuery()
            ->getResult();
	
        $allcities = array();
        foreach ($regions as $region) {
            //exit(\Doctrine\Common\Util\Debug::dump($region->getName())); 
            $cities = $region->getRegionCities();
            foreach ($cities as $city) {
                if (!in_array($city, $allcities)) {
                    $allcities[] = $city;
                }
            }
        }
        $choices = array();
        $numchoices=0;
        $myaddress = $entity->getPobox()->getCustomer()->getBaddress();
        foreach ($myaddress as $address) {
            $mycity = $address->getCity();
            if (in_array($mycity,$allcities)) {
                $choices[] = $address;
                $numchoices++;
            }
        }
        $default = $entity->getPobox()->getCustomer()->getAdrdefault();
        if (!in_array($default->getCity(),$allcities))  {
            if (count($choices) > 0) {
                $default = $choices[0]; 
            } else {
                $default = new Baddress();
            }
        }
        */
        $customer = $pobox->getCustomer();
        $choices = $customer->getBaddress();
        $default = $customer->getAdrdefault();
        $entity->setBaddress($default);
        
        $numchoices = count($choices);
        $form   = $this->createCreateForm($entity, $choices);
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'pobox' => $pobox,
            'numchoices' => $numchoices,
        );
    }

    /**
     * Finds and displays a Alert entity.
     *
     * @Route("/{id}/show", name="alert_show")
     * @Method("GET")
     * @Template()
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Alert')->find($id);

        $user = $this->getUser();
        $admin = $this->isGranted('ROLE_ADMIN') && ($user->getMaincompany() == $entity->getMaincompany());
        $pobox = $user->getPobox();


        if (!$entity)  {
            throw $this->createNotFoundException('No existe la alerta que está buscando');
        }
        if (!$entity->getIsshowed())  {
            throw $this->createNotFoundException('No se puede mostrar la alerta');
        }
        if ($pobox != $entity->getPobox() && (!$admin) ) {
            throw $this->createNotFoundException('No tiene permiso para ver la alerta');
        }

        $deleteForm = $this->createDeleteForm($id);

        $image = null;
        if ($entity->getImageSize() > 0) {
            $image = base64_encode(stream_get_contents($entity->getImageData()));
        }
        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'image' => $image,
        );
    }

    /**
     * Displays a form to edit an existing Alert entity.
     *
     * @Route("/{id}/edit", name="alert_edit")
     * @Method("GET")
     * @Template()
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Alert')->find($id);
        $user = $this->getUser();
        $pobox = $user->getPobox();
        $maincompany = $user->getMaincompany();

        if (!$entity)  {
            throw $this->createNotFoundException('No existe la alerta que está buscando');
        }
        if ($pobox != $entity->getPobox() ) {
            throw $this->createNotFoundException('No tiene permiso para editar la alerta');
        }
        if (($entity->getReceipt()) || (!$entity->getIsshowed())) {
            throw $this->createNotFoundException('No puede editar la alerta');
        }	
        $image = null;
        if ($entity->getImageSize() > 0) {
            $image = base64_encode(stream_get_contents($entity->getImageData()));
        }
        
        $entity->setArrivedate($entity->getArrivedate()->format('m/d/Y'));
        $rs = $em->getRepository('NvCargaBundle:Tariff')->createQueryBuilder('t')
                    ->select('DISTINCT r.id')
                    ->leftjoin('t.region', 'r')
                    ->where('r.id = t.region')
                    //->andWhere('t.maincompany = :maincompany')
                    //->setParameters(array('maincompany' => $maincompany))
                    ->getQuery()
                    ->getResult();
        // $regions = $em->getRepository('NvCargaBundle:Region')->findBy(array('id' => $rs));

        
        $regions = $em->getRepository('NvCargaBundle:Region')->createQueryBuilder('r')
                ->where('r.id IN (:ids)')
                ->setParameter('ids',$rs)
                ->getQuery()
                ->getResult();
        
        $allcities = array();
        foreach ($regions as $region) {
            // exit(\Doctrine\Common\Util\Debug::dump($region->getName())); 
            $cities = $region->getRegionCities();
            foreach ($cities as $city) {
                if (!in_array($city, $allcities)) {
                    $allcities[] = $city;
                }
            }
        }
        $choices = array();
        $numchoices=0;
        $myaddress = $entity->getPobox()->getCustomer()->getBaddress();
        foreach ($myaddress as $address) {
            $mycity = $address->getCity();
            if (in_array($mycity,$allcities)) {
                $choices[] = $address;
                $numchoices++;
            }
        }
        $default = $entity->getPobox()->getCustomer()->getAdrdefault();
        if (!in_array($default->getCity(),$allcities))  {
            if (count($choices) > 0) {
                $default = $choices[0]; 
            } else {
                $default = new Baddress();
            }
        }

        $editForm = $this->createEditForm($entity,$default,$choices);
        $deleteForm = $this->createDeleteForm($id);
	
        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'image' => $image,
            'numchoices' => $numchoices,
        );
    }

    /**
    * Creates a form to edit a Alert entity.
    *
    * @param Alert $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Alert $entity, Baddress $default, array $choices)
    {
        $form = $this->createForm(new AlertType(), $entity, array(
            'action' => $this->generateUrl('alert_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));


        $form->add('submit', 'submit', array('label' => 'Actualizar'));
        $form->remove('tracking');
        $form->add('tracking', null, array('label'=>'Tracking', 'read_only'=>true));
        $em = $this->getDoctrine()->getManager();
        $carriers = $em->getRepository('NvCargaBundle:Carrier')->findBy(['maincompany'=>$this->getuser()->getMaincompany(), 'active'=>true]);
        $form->add('carrier', 'entity', array('label'=>'Carrier ', 'required' => true, 
            'class'=>'NvCargaBundle:Carrier', 'choices'=>$carriers));
        $form->add('baddress', 'entity', array('required' => true, 'label' =>' ', 'class' => 'NvCargaBundle:Baddress',
                        'data_class' => 'NvCarga\Bundle\Entity\Baddress',
                        'data'=> $default,
                        'choices' => $choices, 
                        'expanded' => true, 'multiple' => false));



        return $form;
    }
    /**
     * Edits an existing Alert entity.
     *
     * @Route("/{id}/update", name="alert_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Alert:edit.html.twig")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Alert')->find($id);
        $user = $this->getUser();
        $pobox = $user->getPobox();
        $maincompany = $user->getMaincompany();

        if (!$entity)  {
            throw $this->createNotFoundException('No existe la alerta que está buscando');
        }
        if ($pobox != $entity->getPobox() ) {
            throw $this->createNotFoundException('No tiene permiso para editar la alerta');
        }
        if (($entity->getReceipt()) || (!$entity->getIsshowed())) {
            throw $this->createNotFoundException('No puede editar la alerta');
        }	
        $image = null;
        if ($entity->getImageSize() > 0) {
            $image = base64_encode(stream_get_contents($entity->getImageData()));
        }

        $deleteForm = $this->createDeleteForm($id);
        $entity->setArrivedate($entity->getArrivedate()->format('m/d/Y'));
        $rs = $em->getRepository('NvCargaBundle:Tariff')->createQueryBuilder('t')
                    ->select('DISTINCT r.id')
                    ->leftjoin('t.region', 'r')
                    ->where('r.id = t.region')
                    //->andWhere('t.maincompany = :maincompany')
                    //->setParameters(array('maincompany' => $maincompany))
                        ->getQuery()
                    ->getResult();
        // $regions = $em->getRepository('NvCargaBundle:Region')->findBy(array('id' => $rs));

        
        $regions = $em->getRepository('NvCargaBundle:Region')->createQueryBuilder('r')
                ->where('r.id IN (:ids)')
                        ->setParameter('ids',$rs)
                ->getQuery()
                ->getResult();
        
        $allcities = array();
        foreach ($regions as $region) {
            // exit(\Doctrine\Common\Util\Debug::dump($region->getName())); 
            $cities = $region->getRegionCities();
            foreach ($cities as $city) {
                if (!in_array($city, $allcities)) {
                    $allcities[] = $city;
                }
            }
        }
        $choices = array();
        $numchoices=0;
        $myaddress = $entity->getPobox()->getCustomer()->getBaddress();
        foreach ($myaddress as $address) {
            $mycity = $address->getCity();
            if (in_array($mycity,$allcities)) {
                $choices[] = $address;
                $numchoices++;
            }
        }
        $default = $entity->getPobox()->getCustomer()->getAdrdefault();
        if (!in_array($default->getCity(),$allcities))  {
            if (count($choices) > 0) {
                $default = $choices[0]; 
            } else {
                $default = new Baddress();
            }
        }
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $editForm = $this->createEditForm($entity, $default, $choices);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $entity->upload();
            $entity->setArrivedate(new \DateTime($entity->getArrivedate()));
            $em->flush();

            return $this->redirect($this->generateUrl('alert_show', array('id' => $id)));
        } else {
            $this->get('session')->getFlashBag()->add(
                            'notice',
                            'Hay errores en los datios, por favor, verifique..');
        }

        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'image' => $image,
            'numchoices' => $numchoices,
        );
    }
    /**
     * Deletes a Alert entity.
     *
     * @Route("/{id}/delete", name="alert_delete")
     * @Method("DELETE")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $countalert =  $maincompany->getCountalerts();
	$countalert++;
        $maincompany->setCountalerts($countalert);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Alert')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('No existe la alerta que desea eliminar');
            }
            $user = $this->getUser();
            $admin = ($this->isGranted('ROLE_ADMIN') && ($user->getMaincompany() == $entity->getMaincompany()));
            $pobox = $user->getPobox();
            if (!$entity->getIsshowed())  {
            	throw $this->createNotFoundException('No se puede eliminar la alerta');
            }
            if ($pobox != $entity->getPobox() && (!$admin) ) {
                throw $this->createNotFoundException('No tiene permiso para eliminar la alerta');
            }
            $pobox=$entity->getPobox();
            $maincompany->setCountalerts($countalert--);
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('alert_list', array('id' => $pobox->getId())));
    }

    /**
     * Creates a form to delete a Alert entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('alert_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
   /**
     * search an alert by tracking
     *
     * @Route("/search", name="alert_search")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $track = $request->query->get('tracking');
        $carrier = $request->query->get('carrier');
        $em = $this->getDoctrine()->getManager();
        $result=array();
        $result['id'] = 0;
        $user=$this->getUser();
        $maincompany = $user->getMaincompany();
        if ($track)  {
            if ($carrier) {
                $listalert = $em->getRepository('NvCargaBundle:Alert')->createQueryBuilder('a')
                                                ->where('a.receipt IS NULL')
                                                ->andWhere('a.tracking = :track')
                                                ->andWhere('a.carrier = :carrier')
                                                ->andWhere('a.maincompany = :maincompany')
                                                ->setParameters(array('track'=>$track,'carrier'=>$carrier, 'maincompany'=>$maincompany))
                                                ->getQuery()
                                                ->getResult();
            } else {
                $listalert = $em->getRepository('NvCargaBundle:Alert')->createQueryBuilder('a')
                                                ->where('a.receipt IS NULL')
                                                ->andWhere('a.tracking = :track')
                                                ->andWhere('a.maincompany = :maincompany')
                                                ->setParameters(array('track'=>$track,'maincompany'=>$maincompany))
                                                ->getQuery()
                                                ->getResult();
            }
            $alert=null;
            if ($listalert) {
                $alert = reset($listalert);
            }
            if ($alert) {
                $result['id'] = $alert->getId();
                $result['pobox'] = $alert->getPobox()->getNumber();
                $result['carrier'] = $alert->getCarrier()->getName();
                $result['emailcus'] = $alert->getPobox()->getUser()->getUsername();
            }
        } 
        return new JsonResponse($result); 
    }
    /**
     * search an alert by tracking
     *
     * @Route("/find", name="alert_find")
     * @Template("NvCargaBundle:Alert:index.html.twig")
     */
    public function findAction(Request $request)
    {	
        $track = $request->query->get('tracking');
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $maincompany = $user->getMaincompany();
        if (!$track) {
            throw $this->createNotFoundException('Debe suministrar un número de tracking');
        }
        $entities = $em->getRepository('NvCargaBundle:Alert')->findBy(array('tracking'=>$track,'maincompany'=>$maincompany ));
        $images = array();
        foreach ($entities as $entity) {
            if ($entity->getImageSize() > 0) {
                $image = base64_encode(stream_get_contents($entity->getImageData()));
            } else {
                $image = null;
            }
            $images[]=$image;
        }
        $form = $this->createSearchForm($track);
        return array(
                'entities' => $entities,
                'images' => $images,
                'form'   => $form->createView(),
        );
    }  
}
