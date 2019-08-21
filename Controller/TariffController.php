<?php

namespace NvCarga\Bundle\Controller;

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Tariff;
use NvCarga\Bundle\Form\TariffType;

/**
 * Tariff controller.
 *
 * @Route("/tariff")
 */
class TariffController extends Controller
{

    /**
     * Lists all Tariff entities.
     *
     * @Route("/{idag}/index", name="tariff")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_TARIFF') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_TARIFF')")
     */
    public function indexAction($idag)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();
        $maincompany = $user->getMaincompany();
        echo $agency.'<BR>';


        if (($agency->getType() != 'MASTER' ) && ($agency->getId() != $idag)) {
            throw $this->createNotFoundException('No tiene permisos para consultar las tarifas de esta agencia');
        }
        if ($agency->getType() == 'MASTER' ) {
            $agencies = $em->getRepository('NvCargaBundle:Agency')->findByMaincompany($maincompany);
            $entities = $em->getRepository('NvCargaBundle:Tariff')->findByAgency($agencies);
        } else {
            $entities = $em->getRepository('NvCargaBundle:Tariff')->findByAgency($agency);
            $agencies = null;
        }
        // $agency = $em->getRepository('NvCargaBundle:Agency')->find($idag);

        return array(
            'entities' => $entities,
            'agencies' => $agencies,
        );
    }
    /**
     * Creates a new Tariff entity.
     *
     * @Route("/{idag}/create", name="tariff_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Tariff:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_TARIFF') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request,$idag)
    {
        $entity = new Tariff();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();
        if (($agency->getType() != 'MASTER' ) && ($agency->getId() != $idag)) {
            throw $this->createNotFoundException('No tiene permisos para crear tarifas en esta agencia');
        }
        $agency = $em->getRepository('NvCargaBundle:Agency')->find($idag);
        // $services = $em->getRepository('NvCargaBundle:Servicetype')->findByAgency($agency, array('name' => 'asc'));
        $form   = $this->createCreateForm($entity,$idag);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setLastupdate(new \DateTime());
            // $entity->setName($entity->getService()->getName());
            $entity->setAgency($agency);
            $entity->setActive(true);
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('tariff_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'agency' => $agency,
        );
    }

    /**
     * Creates a form to create a Tariff entity.
     *
     * @param Tariff $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Tariff $entity, $idag)
    {
        $form = $this->createForm(new TariffType($this->getDoctrine()->getManager(), $this->getuser()), $entity, array(
            'action' => $this->generateUrl('tariff_create', array('idag' => $idag)),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));
        $form->add('shippingtype', 'entity', array('label'=> 'Envío',
            'class' => 'NvCargaBundle:Shippingtype',
        ));

        if ($this->getUser()->getAgency()->getType() == "MASTER") {
            $em = $this->getDoctrine()->getManager();
            $agencies = $em->getRepository('NvCargaBundle:Agency')->findByMaincompany($this->getUser()->getMaincompany());
            $form->add('agency', 'entity', array('label' => 'Agencia ', 'class' => 'NvCargaBundle:Agency',
                'choices'=>$agencies));
        } else {
            $form->add('agency', 'entity', array('label' => 'Agencia ', 'class' => 'NvCargaBundle:Agency', 'read_only'  => true, 'disabled' => true)) ;
        }
        return $form;
    }

    /**
     * Displays a form to create a new Tariff entity.
     *
     * @Route("/{idag}/new", name="tariff_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_TARIFF') or has_role('ROLE_ADMIN')")
     */
    public function newAction($idag)
    {
        $entity = new Tariff();

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();
        if (($agency->getType() != 'MASTER' ) && ($agency->getId() != $idag)) {
            throw $this->createNotFoundException('No tiene permisos para crear tarifas en esta agencia');
        }
        $entity->setAgency($agency);

        // $services = $em->getRepository('NvCargaBundle:Servicetype')->findByAgency($agency, array('name' => 'asc'));
        $form   = $this->createCreateForm($entity,$idag);
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Tariff entity.
     *
     * @Route("/{id}/show", name="tariff_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_TARIFF') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_TARIFF')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Tariff')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la tarifa');
        }

        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'agency' => $entity->getAgency(),
            // 'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Tariff entity.
     *
     * @Route("/{id}/edit", name="tariff_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_TARIFF') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Tariff')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la Tarifa.');
        }
        $agency = $entity->getAgency();

        // $services = $em->getRepository('NvCargaBundle:Servicetype')->findByAgency($agency, array('name' => 'asc'));
        $editForm = $this->createEditForm($entity);
        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
           // 'country' => $entity->getCountry(),
          // 'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Tariff entity.
    *
    * @param Tariff $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Tariff $entity)
    {
        $form = $this->createForm(new TariffType($this->getDoctrine()->getManager(), $this->getUser()), $entity, array(
            'action' => $this->generateUrl('tariff_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $em = $this->getDoctrine()->getManager();
        $tariff = $em->getRepository('NvCargaBundle:Guide')->findByTariff($entity);

        if ($tariff) {
            $form->add('shippingtype', 'entity', array('label'=> 'Tipo de envío ',
                    'class' => 'NvCargaBundle:Shippingtype',
                    'data'=> $entity->getShippingtype(), 'read_only'=>true, 'disabled'=>true));
            $form->remove('region');
            $form->remove('measure');
            $form->add('region', 'entity', array('label'=> 'Región ', 'class' => 'NvCargaBundle:Region',  'data'=> $entity->getRegion(), 'read_only'=>true, 'disabled'=>true));
            $form->add('measure', 'entity', array('label' => 'Medida ', 'class' => 'NvCargaBundle:Measure', 'data'=> $entity->getMeasure(), 'read_only'=>true, 'disabled'=>true));
        } else {
            $form->add('shippingtype', 'entity', array('label'=> 'Tipo de servicio ',
                    'class' => 'NvCargaBundle:Shippingtype',
                    'data'=> $entity->getShippingtype()));
        }
        $form->add('active', null, array('label' => 'Activa '));
        $form->add('agency', 'entity', array('label' => 'Agencia ', 'class' => 'NvCargaBundle:Agency', 'read_only'  => true, 'disabled' => true));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Tariff entity.
     *
     * @Route("/{id}/update", name="tariff_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Tariff:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_TARIFF') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Tariff')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la Tarifa.');
        }

        // $deleteForm = $this->createDeleteForm($id);
        $agency = $entity->getAgency();
        // $services = $em->getRepository('NvCargaBundle:Servicetype')->findByAgency($agency, array('name' => 'asc'));
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $entity->setLastupdate(new \DateTime());
            $em->flush();

            return $this->redirect($this->generateUrl('tariff_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            // 'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Tariff entity.
     *
     * @Route("/{id}/delete", name="tariff_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_TARIFF') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
	/*
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Tariff')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Tariff entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('tariff'));
	*/
    }

    /**
     * Creates a form to delete a Tariff entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('tariff_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }

    /**
     * Finds a Tariff entity.
     *
     * @Route("/find", name="tariff_find")
     * @Security("has_role('ROLE_ADMIN_TARIFF') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_TARIFF')")
     */
    public function findAction(Request $request)
    {

        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $em = $this->getDoctrine()->getManager();
        // Get the tariff ID
        $id = $request->query->get('tariff_id');
        $entity = $em->getRepository('NvCargaBundle:Tariff')->find($id);


        if (!$entity) {
            throw $this->createNotFoundException('La tarifa seleccionada no existe');
        }
        // Construccion de la respuesta a enviar.
        // esto también se puede hacer con un componente especial de JSON para SYMFONY
        $result = array();
        $result['measure'] = $entity->getMeasure()->getName();
        $result['insurance'] = $entity->getInsurance();
        $result['tax'] = $entity->getTax();
        $result['dimentional'] = $entity->getDimentional();
        $result['begin'] = $entity->getBegin();
        $result['until'] = $entity->getUntil();
        $result['minimun'] = $entity->getMinimun();
        $result['valuemeasure'] = $entity->getValueMeasure();
        $result['valuemin'] = $entity->getValueMin();
        $result['minimunlimit'] = $entity->getMinimunLimit();
        $result['volumeprice'] = $entity->getVolumePrice();
        $result['additional'] = $entity->getAdditional();
        $result['labeladditional'] = $entity->getLabelAdditional();
        $result['insuranceper'] = $entity->getInsurancePer();
        $result['taxper'] = $entity->getTaxPer();
        $result['weightpay'] = $entity->getWeightpay();
        return new JsonResponse($result);
    }
    /**
     * Finds a Tariff entity.
     *
     * @Route("/select", name="tariffs_select")
     * @Security("has_role('ROLE_ADMIN_TARIFF') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_TARIFF')")
     */
    public function selectAction(Request $request)
    {
        $maincompany = $this->getUser()->getMaincompany();
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $agencyid = $request->query->get('agency_id');
        $cityid = $request->query->get('city_id');
        $countryid = $request->query->get('country_id');

        // throw $this->createNotFoundException('Ag= '. $agencyid . ' City= ' . $cityid);
        $variables= 'Ag= '. $agencyid . ' City= ' . $cityid;
        // exit(\Doctrine\Common\Util\Debug::dump($variables));
        $entities = null;
        if ($agencyid) {
            $em = $this->getDoctrine()->getManager();
            $agency = $em->getRepository('NvCargaBundle:Agency')->find($agencyid);
            if (($countryid) && ($cityid)) {
                $city = $em->getRepository('NvCargaBundle:City')->find($cityid);
                $country = $em->getRepository('NvCargaBundle:Country')->find($countryid);

                $allregions = [];
                $regionALL = $em->getRepository('NvCargaBundle:Region')->createQueryBuilder('r')
                        ->where('r.maincompany =:thecompany')
                        ->andWhere('r.country =:country')
                        ->andwhere('r.name LIKE :todas OR r.name LIKE :pais OR r.name LIKE :general' )
                        ->setParameters(['thecompany'=>$maincompany,'country'=>$country, 'todas'=>'%todas% ', 'pais'=>'%'.$country.'%', 'general'=>'%general%'])
                        ->getQuery()
                        ->getResult();
                foreach ($regionALL as $region) {
                    $allregions[]=$region->getId();
                }
                if ($city) {
                    $regions = $city->getRegions();
                    foreach ($regions as $region) {
                        if ($region->getMaincompany() == $maincompany) {
                            if (!in_array($region->getId(), $allregions)) {
                                $allregions[] = $region->getId();
                            }
                        }
                    }
                }

                $entities = $em->getRepository('NvCargaBundle:Tariff')->findBy(array('agency'=> $agency, 'region' => $allregions, 'active'=>true));
            } else {
                $entities = $em->getRepository('NvCargaBundle:Tariff')->findBy(array('agency'=> $agency, 'active'=>true));
            }

        }
        $result = array();
        $count = 0;
        // exit(\Doctrine\Common\Util\Debug::dump($entities));
        foreach ($entities as $entity) {
            $result[$count]['tid'] = $entity->getId();
            $result[$count]['name'] = $entity->getName();
            $result[$count]['measure'] = $entity->getMeasure()->getLabel();
            $result[$count]['insurance'] = $entity->getInsurance();
            $result[$count]['tax'] = $entity->getTax();
            $result[$count]['dimentional'] = $entity->getDimentional();
            $result[$count]['begin'] = $entity->getBegin();
            $result[$count]['until'] = $entity->getUntil();
            $result[$count]['minimun'] = $entity->getMinimun();
            $result[$count]['valuemeasure'] = $entity->getValueMeasure();
            $result[$count]['valuemin'] = $entity->getValueMin();
            $result[$count]['minimunlimit'] = $entity->getMinimunLimit();
            $result[$count]['volumeprice'] = $entity->getVolumePrice();
            $result[$count]['additional'] = $entity->getAdditional();
            $result[$count]['labeladditional'] = $entity->getLabelAdditional();
            $result[$count]['insuranceper'] = $entity->getInsurancePer();
            $result[$count]['taxper'] = $entity->getTaxPer();
            $count++;
        }
       	return new JsonResponse($result);
    }
}
