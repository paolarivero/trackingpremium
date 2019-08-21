<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Agency;
use NvCarga\Bundle\Entity\Warehouse;
use NvCarga\Bundle\Form\AgencyType;
use NvCarga\Bundle\Entity\Tariff;

/**
 * Agency controller.
 *
 * @Route("/agency")
 */
class AgencyController extends Controller
{

    /**
     * Lists all Agency entities.
     *
     * @Route("/index", name="agency")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_AGENCY') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_AGENCY')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        
        if ($user->getAgency()->getType() == 'MASTER') {
            $entities = $em->getRepository('NvCargaBundle:Agency')->findByMaincompany($user->getMaincompany());
        } else {
            $entities = $em->getRepository('NvCargaBundle:Agency')->find($user->getAgency());
        }

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Agency entity.
     *
     * @Route("/create", name="agency_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Agency:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_AGENCY') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $user = $this->getUser(); 
        $maincompany = $user->getMaincompany();
        $em = $this->getDoctrine()->getManager();
        
        $entity = new Agency();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        if ($user->getAgency()->getType() != "MASTER" ) {
            $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        'Solamente la Agencia MASTER puede crear otras aganecias.');
            goto next;
            // throw $this->createAccessDeniedException('');
        }
        $countagency =  $maincompany->getCountagencies();
        
        $plan = $maincompany->getPlan();                    
        if (($plan->getAgencies()) && ($countagency >= $plan->getMaxagencies())) {
            $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        'La empresa ha  alcanzado el número MÁXIMO de AGENCIAS permitidas.');
            goto next;
            //        throw $this->createNotFoundException('La empresa ha  alcanzado el número MÁXIMO de AGENCIAS permitidas.');
        }
        $countagency++;
        $maincompany->setCountagencies($countagency);
        // BUSCAR LAS MEDIDAS Y LOS TIPOS DE ENVIO
        $lb = $em->getRepository('NvCargaBundle:Measure')->findOneByName('Lb');
        $cf = $em->getRepository('NvCargaBundle:Measure')->findOneByName('CF');
        $servAereo = $em->getRepository('NvCargaBundle:Shippingtype')->findOneByName('Aéreo');
        $servMar = $em->getRepository('NvCargaBundle:Shippingtype')->findOneByName('Marítimo');
        
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
           
            $id = $form->get("cityid")->getData();
            if (!$id) {
                $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        'Debe seleccionar una ciudad. ');
                goto next;
               // throw $this->createNotFoundException('Debe seleccionar una ciudad');
            }
            
            $agename = $em->getRepository('NvCargaBundle:Agency')->findOneBy(['name' =>$entity->getName(), 'maincompany'=>$maincompany]);
            if ($agename) {
                $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        'Ya existe una agencia con ese nombre en la empresa.');
                goto next;
                // throw $this->createNotFoundException('Ya existe una agencia con ese nombre en la empresa');
            }
            $ageemail = $em->getRepository('NvCargaBundle:Agency')->findOneBy(['email' =>$entity->getEmail(), 'maincompany'=>$maincompany]);
            if ($ageemail) {
                $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        'Ya existe una agencia con ese email en la empresa.');
                goto next;
                // throw $this->createNotFoundException('Ya existe una agencia con ese email en la empresa');
            }
            $tipomaster = $em->getRepository('NvCargaBundle:Agencytype')->findOneBy(array('name'=>'MASTER'));
            $master = $em->getRepository('NvCargaBundle:Agency')->findOneBy(array('type'=>$tipomaster, 'maincompany'=>$maincompany));
            if (!$master) {  // la primera agencia agregada será la master
                $entity->setType($tipomaster);
                $entity->setMainCompany($maincompany);
            } else { // Ya existe una agencia master
                if ($entity->getType() === $tipomaster)  { //Solo puede existir una agencia master
                    $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        'Ya existe una agencia master en la empresa.');
                    goto next;
                    //throw $this->createNotFoundException('Ya existe una agencia master en la empresa');
                } else {
                    $type=$em->getRepository('NvCargaBundle:Agencytype')->findOneByName('RECEPTORA');
                    $entity->setParent($master);
                    $entity->setMaincompany($master->getMaincompany()); 
                    $entity->setType($type);
                }
            }
            $city = $em->getRepository('NvCargaBundle:City')->find($id);
            $status = $em->getRepository('NvCargaBundle:Agencystatus')->findOneByName('ACTIVA');
            $entity->setCreationdate(new \DateTime());
            $entity->setLastupdate(new \DateTime());
            $entity->setStatus($status);
            $entity->setCity($city);
            
            $warehouse = new Warehouse();
            $warehouse->setName('Warehouse ' .  $entity->getName());
            $warehouse->setAddress($entity->getAddress());
            $warehouse->setDescription('Bodega de la agencia:' . $entity->getName());
            $warehouse->setZip($entity->getZip());
            $warehouse->setCreationdate(new \DateTime());
            $warehouse->setLastupdate(new \DateTime());
            $warehouse->setCity($entity->getCity());
            $warehouse->setMainCompany($maincompany);
            $warehouse->setAgency($entity);
            $entity->setWarehouse($warehouse);
            
            $em->persist($warehouse);
            $em->persist($entity);
            
            $countries = $maincompany->getCountries()->toArray();
            foreach ($countries as $country) {
                $region = $em->getRepository('NvCargaBundle:Region')->createQueryBuilder('r')
                            ->where('r.maincompany =:thecompany')
                            ->andWhere('r.country =:country')
                            ->andWhere('r.name LIKE :name')
                            ->setParameters(['thecompany'=>$maincompany, 'country'=>$country, 'name'=>'%Todas%'. $country .'%'])
                            ->getQuery()
                            ->getOneOrNullResult();
                
                // AGREGAR DOS TARIFAS BASICAS
                $name = 'Aérea General ' . $country->getName();
                $tariff1 = new Tariff();
                $tariff1->setAgency($entity);
                $tariff1->setRegion($region); 
                $tariff1->setLastupdate(new \DateTime());
                $tariff1->setShippingtype($servAereo);
                $tariff1->setName('Tarifa ' . $name);
                $tariff1->setMeasure($lb);
                $tariff1->setInsurance(false);
                $tariff1->setInsurancePer(0);
                $tariff1->setTax(false);
                $tariff1->setTaxPer(0);
                $tariff1->setDimentional(false);
                $tariff1->setCost(0);
                $tariff1->setBegin(0.01);
                $tariff1->setUntil(5000);
                $tariff1->setMinimun(0.01);
                $tariff1->setValueMeasure(1);
                $tariff1->setValueMin(1);
                $tariff1->setMinimunLimit('Total');
                $tariff1->setProfitAg(0);
                $tariff1->setVolumePrice(0);
                $tariff1->setProfitAgv(0);
                $tariff1->setAdditional(0);
                $tariff1->setLabelAdditional('');
                $tariff1->setActive(true);
                $em->persist($tariff1);
                
                $name = 'Marítima General '  . $country->getName();
                $tariff2 = new Tariff();
                $tariff2->setAgency($entity);
                $tariff2->setRegion($region); 
                $tariff2->setLastupdate(new \DateTime());
                $tariff2->setShippingtype($servMar);
                $tariff2->setName('Tarifa ' . $name);
                $tariff2->setMeasure($cf);
                $tariff2->setInsurance(false);
                $tariff2->setInsurancePer(0);
                $tariff2->setTax(false);
                $tariff2->setTaxPer(0);
                $tariff2->setDimentional(false);
                $tariff2->setCost(0);
                $tariff2->setBegin(0.01);
                $tariff2->setUntil(5000);
                $tariff2->setMinimun(0.01);
                $tariff2->setValueMeasure(1);
                $tariff2->setValueMin(1);
                $tariff2->setMinimunLimit('Total');
                $tariff2->setProfitAg(0);
                $tariff2->setVolumePrice(0);
                $tariff2->setProfitAgv(0);
                $tariff2->setAdditional(0);
                $tariff2->setLabelAdditional('');
                $tariff2->setActive(true);
                $em->persist($tariff2);
            }
            
            $em->flush();
            // FIN DE TARIFAS BASICAS
            
            
            $em->flush();

            return $this->redirect($this->generateUrl('agency_show', array('id' => $entity->getId())));
        }
        next:
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Agency entity.
     *
     * @param Agency $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Agency $entity)
    {
        $form = $this->createForm(new AgencyType(), $entity, array(
            'action' => $this->generateUrl('agency_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Agency entity.
     *
     * @Route("/new", name="agency_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_AGENCY') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $user = $this->getUser(); 
        $maincompany = $user->getMaincompany();
        if ($user->getAgency()->getType() != "MASTER" ) {
            throw $this->createAccessDeniedException('');
        }
        $entity = new Agency();
        $countagency =  $maincompany->getCountagencies();
        $plan = $maincompany->getPlan();                    
        if (($plan->getAgencies()) && ($countagency >= $plan->getMaxagencies())) {
            $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        'La empresa ha  alcanzado el número MÁXIMO de AGENCIAS permitidas.');
        }
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Agency entity.
     *
     * @Route("/{id}/show", name="agency_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_AGENCY') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_AGENCY')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Agency')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No exite la agencia');
        }

        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            // 'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Agency entity.
     *
     * @Route("/{id}/edit", name="agency_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_AGENCY') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user->getAgency()->getType() != "MASTER" ) {
            throw $this->createAccessDeniedException('');
        }
        $entity = $em->getRepository('NvCargaBundle:Agency')->findOneBy(['id'=>$id,'maincompany'=>$this->getUser()->getMaincompany()]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la agencia');
        }

        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Agency entity.
    *
    * @param Agency $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Agency $entity)
    {
        $form = $this->createForm(new AgencyType(), $entity, array(
            'action' => $this->generateUrl('agency_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $form->remove('name');
        $form->add('name', null, array('label'=> 'Nombre ', 'data'=>$entity->getName(), 'read_only'=> true ));
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Agency entity.
     *
     * @Route("/{id}/update", name="agency_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Agency:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_AGENCY') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $entity = $em->getRepository('NvCargaBundle:Agency')->findOneBy(['id'=>$id,'maincompany'=>$this->getUser()->getMaincompany()]);
        $currentname = $entity->getName();
        if (!$entity) {
            $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        'No existe la agencia.');
            goto next;
            // throw $this->createNotFoundException('No existe la agencia');
        }
        if (($user->getAgency()->getType() != "MASTER" ) || ($user->getAgency() != $entity))  {
            $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        'No tiene permiso para EDITAR la agencia.');
            goto next;
            // throw $this->createAccessDeniedException('');
        }
        // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $cityid = $editForm->get("cityid")->getData();
            if (!$cityid) {
                $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        'Debe seleccionar una ciudad.');
                goto next;
                // throw $this->createNotFoundException('Debe seleccionar una ciudad');
            }
            
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('NvCargaBundle:City')->find($cityid);
            $entity->setCity($city);
            $warehouse = $entity->getWarehouse();
            $warehouse->setCity($city);
            $warehouse->setAddress($entity->getAddress());
            $warehouse->setZip($entity->getZip());
            $warehouse->setLastupdate(new \DateTime());
            $agename = $em->getRepository('NvCargaBundle:Agency')->findOneByName($entity->getName());
            if (($agename) && ($entity->getName() != $currentname)) {
                $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        'Ya existe una agencia con ese nombre en la empresa.');
                goto next;
                // throw $this->createNotFoundException('Ya existe una agencia con ese nombre en la empresa');
            }
            $entity->setLastupdate(new \DateTime());
            $em->flush();

            return $this->redirect($this->generateUrl('agency_show', array('id' => $id)));
        } else {
            $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        'Hay errores en la forma.');
        }
        next:
        return array(
               'entity'      => $entity,
               'edit_form'   => $editForm->createView(),
                // 'delete_form' => $deleteForm->createView(),
        );
    }
   
}
