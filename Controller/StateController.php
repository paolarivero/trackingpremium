<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\State;
use NvCarga\Bundle\Form\StateType;

/**
 * State controller.
 *
 * @Route("/state")
 */
class StateController extends Controller
{

    /**
     * Lists all State entities.
     *
     * @Route("/index", name="state")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_STATE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_STATE')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user =$this->getUser();
        $maincompany = $user->getMaincompany();
        
        $entities = $em->getRepository('NvCargaBundle:State')->findByCountry($maincompany->getCountries()->toArray());
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
     * Creates a new State entity.
     *
     * @Route("/create", name="state_create")
     * @Method("POST")
     * @Template("NvCargaBundle:State:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_STATE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new State();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $state = $em->getRepository('NvCargaBundle:State')->findOneBy(array('country' => $entity->getCountry(), 'name' => $entity->getName()));
            if ($state) {
                throw $this->createNotFoundException('Ya se ha usado el nombre ' . $entity->getName() . ' en ' . $entity->getCountry());
            }
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('state_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a State entity.
     *
     * @param State $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(State $entity)
    {        

        $form = $this->createForm(new StateType($this->getDoctrine()->getManager()), $entity, array(
            'action' => $this->generateUrl('state_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Creates a form to create a State entity.
     *
     * @param State $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSpecialForm(State $entity)
    {
        $form = $this->createForm(new StateType($this->getDoctrine()->getManager()), $entity, array(
            'action' => $this->generateUrl('state_create'),
            'method' => 'POST',
        ));

        $form->add('save', 'button', array('label' => 'Guardar'));

        return $form;
    }
    /**
     * Displays a form to create a new State entity.
     *
     * @Route("/new", name="state_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_STATE') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new State();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new State entity.
     *
     * @Route("/new_special", name="state_newspecial")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_STATE') or has_role('ROLE_ADMIN')")
     */
    public function new_specialAction()
    {
        $entity = new State();
        $form   = $this->createSpecialForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Finds and displays a State entity.
     *
     * @Route("/{id}/show", name="state_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_STATE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_STATE')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:State')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find State entity.');
        }

        return array(
            'entity'      => $entity,
        );
    }


    /**
     * @Route("/states", name="select_states")
     */
    public function ajaxAction(Request $request) {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        // Get the country ID
        $id = $request->query->get('country_id');
        $result = array();
        // Return a list of states
        $repo = $this->getDoctrine()->getManager()->getRepository('NvCargaBundle:State');
        $states = $repo->findByCountry($id, array('name' => 'asc'));
        foreach ($states as $state) {
            $result[$state->getName()] = $state->getId();
        }
        return new JsonResponse($result);
    }
    /**
     * Add a new State entity.
     * @Route("/add", name="state_add")
     * @Security("has_role('ROLE_ADMIN_STATE') or has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
	if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $entity = new State();
        $em = $this->getDoctrine()->getManager();
	$name = $request->query->get('state_name');
	$country = $request->query->get('state_country'); 
	$error=false;
	if (!$name) {
		$result='Error: Debe seleccionar un nombre de estado/provincia';
		return new JsonResponse($result);
	}
	if (!$country) {
		$result='Error:Debe seleccionar un país para el estado/provincia';
		return new JsonResponse($result);
	}
	$thecountry = $em->getRepository('NvCargaBundle:Country')->find($country);
	if (!$thecountry) {
		$result='Error: El país seleccionado no existe';
        	return new JsonResponse($result);
	}
	$state = $em->getRepository('NvCargaBundle:State')->findOneBy(array('country' => $country, 'name' => $name));
	if ($state) {
		$result='Error: Existe un estado/provincia con ese nombre en ese país';
        	return new JsonResponse($result);
	}
	$entity->setName($name);
	$entity->setCountry($thecountry);
        $em->persist($entity);
        $em->flush();
	$result='El estado/provincia ha sido creado satisfactoriamente';
        return new JsonResponse($result);        
    }
}
