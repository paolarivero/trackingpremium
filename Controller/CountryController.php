<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Country;
use NvCarga\Bundle\Form\CountryType;

/**
 * Country controller.
 *
 * @Route("/country")
 */
class CountryController extends Controller
{

    /**
     * Lists all Country entities.
     *
     * @Route("/index", name="country")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_COUNTRY') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_COUNTRY')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Country')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Country entity.
     *
     * @Route("/create", name="country_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Country:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_MAINCOMPANY')")
     */
    public function createAction(Request $request)
    {
        $entity = new Country();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setName(strtoupper($entity->getName()));
            $country = $em->getRepository('NvCargaBundle:Country')->findOneByName($entity->getName());
            if ($country) {
            throw $this->createNotFoundException('Existe un país con ese nombre..');
            }
            $country = $em->getRepository('NvCargaBundle:Country')->findOneBycode($entity->getCode());
            if ($country) {
            throw $this->createNotFoundException('Existe un país con ese código..');
            }
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('country_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Country entity.
     *
     * @param Country $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Country $entity)
    {
        $form = $this->createForm(new CountryType(), $entity, array(
            'action' => $this->generateUrl('country_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Country entity.
     *
     * @Route("/new", name="country_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_MAINCOMPANY')")
     */
    public function newAction()
    {
        $entity = new Country();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Country entity.
     *
     * @Route("/{id}/show", name="country_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_COUNTRY') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_COUNTRY')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Country')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Country entity.');
        }

        return array(
            'entity'      => $entity,
        );
    }
}
