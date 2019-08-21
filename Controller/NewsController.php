<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\News;
use NvCarga\Bundle\Form\NewsType;

/**
 * News controller.
 *
 * @Route("/news")
 */
class NewsController extends Controller
{

    /**
     * Lists all News entities.
     *
     * @Route("/index", name="news")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() //TODOS PUEDEN VER LAS NOTICIAS
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany= $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:News')->findByMaincompany($maincompany);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new News entity.
     *
     * @Route("/create", name="news_create")
     * @Method("POST")
     * @Template("NvCargaBundle:News:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_NEWS') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new News();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $maincompany= $this->getUser()->getMaincompany();

        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $entity->setMaincompany($maincompany);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('news_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a News entity.
     *
     * @param News $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(News $entity)
    {
        $form = $this->createForm(new NewsType(), $entity, array(
            'action' => $this->generateUrl('news_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new News entity.
     *
     * @Route("/new", name="news_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_NEWS') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new News();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a News entity.
     *
     * @Route("/{id}", name="news_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) //TODOS PUEDEN VER UNA NOTICIA EN PARTICULAR
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany= $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:News')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find News entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing News entity.
     *
     * @Route("/{id}/edit", name="news_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_NEWS') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany= $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:News')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find News entity.');
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
    * Creates a form to edit a News entity.
    *
    * @param News $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(News $entity)
    {
        $form = $this->createForm(new NewsType(), $entity, array(
            'action' => $this->generateUrl('news_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing News entity.
     *
     * @Route("/{id}/update", name="news_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:News:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_NEWS') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany= $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:News')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find News entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('news_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a News entity.
     *
     * @Route("/{id}/delete", name="news_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_NEWS') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $maincompany= $this->getUser()->getMaincompany();
            $entity = $em->getRepository('NvCargaBundle:News')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find News entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('news'));
    }

    /**
     * Creates a form to delete a News entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('news_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
