<?php

namespace NvCarga\Bundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Servtoguide;

/**
 * Servtoguide controller.
 *
 * @Route("/servtoguide")
 */
class ServtoguideController extends Controller
{

    /**
     * Lists all Servtoguide entities.
     *
     * @Route("/", name="servtoguide")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Servtoguide')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a Servtoguide entity.
     *
     * @Route("/{id}", name="servtoguide_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Servtoguide')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Servtoguide entity.');
        }

        return array(
            'entity'      => $entity,
        );
    }
}
