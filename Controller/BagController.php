<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use NvCarga\Bundle\Entity\Bag;
use NvCarga\Bundle\Form\BagType;

/**
 * Bag controller.
 *
 * @Route("/bag")
 */
class BagController extends Controller
{

    /**
     * Lists all Bag entities.
     *
     * @Route("/index", name="bag")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $agency = $this->getUser()->getAgency();
        $maincompany =  $this->getUser()->getMaincompany();
        $type = $agency->getType();
        $all = $em->getRepository('NvCargaBundle:Bag')->findBy(['maincompany'=> $maincompany]);	
        if ($type == "MASTER") {
            $entities = $all;
            $agencies = $em->getRepository('NvCargaBundle:Agency')->findBy(['maincompany'=> $maincompany]);	
        } else {
            $agencies = null;
            $entities = [];
            foreach ($all as $entity) {
                if ($entity->getAgency() === $agency()) {
                    $entities[] = $entity;	
                }
            } 
        }
        return array(
                'entities' => $entities,
                'agencies' => $agencies,
        );
    }
    /**
     * Creates a new Bag entity.
     *
     * @Route("/create", name="bag_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Bag:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
     /*
    public function createAction(Request $request)
    {
        $entity = new Bag();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $maincompany =  $this->getUser()->getMaincompany();

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->setMaincompany($maincompany);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('bag_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
*/
    /**
     * Displays a form to create a new Bag entity.
     *
     * @Route("/new", name="bag_new")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function newAction()
    {
       
        $user = $this->getUser();
        $agency = $user->getAgency();
        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('NvCargaBundle:Guide')->findBy(array(), array('pieces' => 'ASC'));
        $entities = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                                ->where('g.agency = :ag')
                                ->andwhere('g.bag IS NULL')
                                ->andwhere('g.consol IS NULL')
                                ->setParameters(array('ag' => $agency))
                                ->addOrderBy('g.shippingtype', 'ASC')
                                ->addOrderBy('g.countryto', 'ASC')
                                ->getQuery()
                                ->getResult(); 

        
        reset($entities);
        $current = current($entities);
        if ($current) {
            $shipt = $current->getShippingtype();
            $country = $current->getCountryto();
        }
        $allgroup = array();
        $group = array();

        foreach ($entities as $entity ) {
            if (($entity->getShippingtype() != $shipt) || ($entity->getCountryto() != $country)) {
                $allgroup[]=$group;
                $shipt = $entity->getShippingtype();
                $country = $entity->getCountryto();
                unset($group);
                $group= array();	
            } 
            $group[] = $entity;	
        }
        $allgroup[] = $group;
        return array(
                'entities' => $entities,
                'allgroup' => $allgroup,
        );
    }

    /**
     * Finds and displays a Bag entity.
     *
     * @Route("/{id}/show", name="bag_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany =  $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Bag')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la bolsa');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Bag entity.
     *
     * @Route("/{id}/edit", name="bag_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany =  $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Bag')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la bolsa');
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
    * Creates a form to edit a Bag entity.
    *
    * @param Bag $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
    */
    private function createEditForm(Bag $entity)
    {
        $form = $this->createForm(new BagType(), $entity, array(
            'action' => $this->generateUrl('bag_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Bag entity.
     *
     * @Route("/{id}/update", name="bag_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Bag:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany =  $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Bag')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la bolsa');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('bag_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Bag entity.
     *
     * @Route("/{id}/delete", name="bag_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        $maincompany=$this->getuser()->getMaincompany();
        $countbag = $maincompany->getCountbags();

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $maincompany =  $this->getUser()->getMaincompany();
            $entity = $em->getRepository('NvCargaBundle:Bag')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

            if (!$entity) {
                throw $this->createNotFoundException('No existe la bolsa.');
            }
            $maincompany->setCountbags($countbag--);
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('bag'));
    }

    /**
     * Creates a form to delete a Bag entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('bag_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
     /**
     * Generate  a bag for printer
     * 
     * @Route("/{id}/print", name="bag_print")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function printAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany =  $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Bag')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);
	
        if (!$entity) {
            throw $this->createNotFoundException('No existe la bolsa');
        }
        
        $viewoption = $this->getView('Bag', 'print');
        
        return  $this->render($viewoption,array('entity'=> $entity)); 
        /*
        return array(
            'entity'      => $entity,
        );
        */
    }
    /**
     * Export to PDF
     * 
     * @Route("/{id}/labelpdf", name="bag_labelpdf")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function labelpdfAction($id)
    {
	$em = $this->getDoctrine()->getManager();

        $maincompany =  $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Bag')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la bolsa');
        }
        $viewoption = $this->getView('Bag', 'label');
        
        $html = $this->renderView($viewoption, array('entity' => $entity));
        // exit(\Doctrine\Common\Util\Debug::dump($html));
        $number = $entity->getNumber(); 
        $filename = sprintf('label-bag-%s.pdf', transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $number));

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                'orientation' => 'portrait',
                'margin-top'    => 8,
                'margin-right'  => 7,
                'margin-bottom' => 8,
                'margin-left'   => 7, 
                'enable-javascript' => true, 
                'javascript-delay' => 1000, 
                'no-stop-slow-scripts' => true, 
                'no-background' => false, 
                'lowquality' => false,
                'encoding' => 'utf-8',
                'images' => true,
                'cookie' => array(),
                'page-size' => 'A7',
                'dpi' => 300,
                'image-dpi' => 300,
                'enable-external-links' => true,
                'enable-internal-links' => true
            )),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
            ]
        );
    }
    /**
     * Export to PDF
     * 
     * @Route("/{id}/printpdf", name="bag_printpdf")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function printfpdfAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany =  $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Bag')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la bolsa');
        }
        
        $viewoption = $this->getView('Bag', 'print');
        
        //return  $this->render($viewoption,array('entity'=> $entity,'services' => $services)); 
        
        $html = $this->renderView($viewoption, array('entity' => $entity));
        // exit(\Doctrine\Common\Util\Debug::dump($html));
        $number = $entity->getNumber(); 
        $filename = sprintf('print-guide-%s.pdf', transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $number));

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                'orientation' => 'portrait',
                'margin-top'    => 10,
                'margin-right'  => 12,
                'margin-bottom' => 10,
                'margin-left'   => 12, 
                'enable-javascript' => true, 
                'javascript-delay' => 1000, 
                'no-stop-slow-scripts' => true, 
                'no-background' => false, 
                'lowquality' => false,
                'encoding' => 'utf-8',
                'images' => true,
                'cookie' => array(),
                'page-size' => 'A4',
                'dpi' => 300,
                'image-dpi' => 300,
                'enable-external-links' => true,
                'enable-internal-links' => true
            )),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
            ]
        );
    }
   /**
     * Creates a form to create a Bag entity.
     *
     * @param Bag $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    private function createReportForm(Bag $entity)
    {
        $form = $this->createForm(new BagType(), $entity, array(
            'action' => $this->generateUrl('bag_report', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Recibir'));

        return $form;
    }
    /**
     * Change status to Bag entity.
     *
     * @Route("/{id}/report", name="bag_report")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function reportAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $agency = $this->getUser()->getAgency();

        if ($agency->getType() != 'MASTER' ) {
            throw $this->createNotFoundException('Esta acción solo se puede hacerse en la agencia Master');
        }
        $maincompany =  $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Bag')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la bolsa');
        }
        if ($entity->getStatus() != "LISTA") {
            throw $this->createNotFoundException('La bolsa '. $entity->getNumber() . ' ya se reportó');
        }
        $form = $this->createReportForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setStatus("ENTREGADA");
            $em->flush();
            return $this->redirect($this->generateUrl('bag_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Finds and displays a  Consolidated to include the Guide
     *
     * @Route("/{id}/toconsol", name="bag_toconsol")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
     public function toconsolAction($id) {
        $em = $this->getDoctrine()->getManager();

        $maincompany =  $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Bag')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la bolsa');
        }
        $translator = $this->get('translator');
        if ($entity->getStatus() == "CONSOLIDADA") {
            throw $this->createNotFoundException('Ya están en un ' . $translator->trans('Consolidados'));
        }
        
        $agency = $entity->getAgency();
        $guide = $entity->getGuides()->first();
        $countryto = $guide->getCountryto();
        $shiptype = $guide->getShippingtype();
        
        $listconsol = $em->getRepository('NvCargaBundle:Consolidated')->createQueryBuilder('c')
                    ->where('c.agency = :ag')
                    ->andwhere('c.countryto = :ct')
                    ->andwhere('c.shippingtype = :st')
                    ->andwhere('c.isopen = 1')
                    ->setParameters(array('ag' => $agency, 
                            'ct' => $countryto, 'st'=>$shiptype ))
                    ->orderBy('c.id', 'ASC')
                    ->getQuery()
                    ->getResult();
        if (count($listconsol) == 0) {
            throw $this->createNotFoundException('No existen ' . $translator->trans('Consolidados') .  ' en la agencia donde se pueda incluir');
        }
        return array(
            'entity' => $entity,
            'listconsol' => $listconsol,
        );
    }   
     
    /**
     * Finds and displays a  Consolidated to include the Guide
     *
     * @Route("/listtomaster", name="bag_listtomaster")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function listtomasterAction()
    {
        $em = $this->getDoctrine()->getManager();
	
        $agency = $this->getUser()->getAgency();
        if ($agency->getType() != 'MASTER' ) {
            throw $this->createNotFoundException('Esta acción solo se puede hacerse en la agencia Master');
        } 
        $maincompany =  $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Bag')->findBy(['status'=>'LISTA', 'maincompany'=>$maincompany]);
        // $entities = $em->getRepository('NvCargaBundle:Bag')->findByStatus('LISTA');
                
        return array(
            'entities' => $entities,
        );
    }
    /**
     * Change status to Bag entity.
     *
     * @Route("/{id}/report1", name="bag_report1")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function report1Action(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $agency = $this->getUser()->getAgency();

        if ($agency->getType() != 'MASTER' ) {
            throw $this->createNotFoundException('Esta acción solo se puede hacerse en la agencia Master');
        }
        $maincompany =  $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Bag')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la bolsa');
        }
        if ($entity->getStatus() != "LISTA") {
            throw $this->createNotFoundException('La bolsa '. $entity->getNumber() . ' ya se reportó');
        }
        $entity->setStatus("ENTREGADA");
        $em->flush();
        return $this->redirect($this->generateUrl('bag_show', array('id' => $entity->getId())));
    }
    /**
     * Finds and displays a  list of bags 
     *
     * @Route("/listtoconsol", name="bag_listtoconsol")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function listtoconsolAction()
    {
        $em = $this->getDoctrine()->getManager();
	
        $agency = $this->getUser()->getAgency();
        if ($agency->getType() != 'MASTER' ) {
            throw $this->createNotFoundException('Esta acción solo se puede hacer en la agencia Master');
        } 
        $maincompany =  $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Bag')->findBy(['status'=>'ENTREGADA', 'maincompany'=>$maincompany]);
                
        return array(
            'entities' => $entities,
        );
    }
  /**
     * Creates a form to drop a Bag entity.
     *
     * @param Bag $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    private function createCancelForm(Bag $entity)
    {
        $form = $this->createForm(new BagType(), $entity, array(
            'action' => $this->generateUrl('bag_cancel', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Anular'));

        return $form;
    }
    /**
     * Change status to Bag entity.
     *
     * @Route("/{id}/cancel", name="bag_cancel")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
     public function cancelAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $agency = $this->getUser()->getAgency();
        $maincompany =  $this->getUser()->getMaincompany();
        $countbag = $maincompany->getCountbags();
        $entity = $em->getRepository('NvCargaBundle:Bag')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);
        if (!$entity) {
                throw $this->createNotFoundException('No existe la bolsa');
        }
        if ($entity->getStatus() != "LISTA") {
            throw $this->createNotFoundException('La bolsa '. $entity->getNumber() . ' no se puede anular');
        }
        if ($agency != $entity->getAgency() ) {
            throw $this->createNotFoundException('Esta acción solo se puede hacer en la agencia que creó la bolsa');
        }
        $guides = $entity->getGuides();
        foreach ($guides as $guide) {
            $guide->setBag(null);
            $entity->removeGuide($guide);
        }
        $maincompany->setCountbags($countbag--);
        $em->remove($entity);
        $em->flush();
        return $this->redirect($this->generateUrl('bag'));
        }
     
     /*
    public function cancelAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $agency = $this->getUser()->getAgency();

        $maincompany =  $this->getUser()->getMaincompany();
        
        $countbag = $maincompany->getCountbags();
        $entity = $em->getRepository('NvCargaBundle:Bag')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
                throw $this->createNotFoundException('No existe la bolsa');
            }
        if ($entity->getStatus() != "LISTA") {
            throw $this->createNotFoundException('La bolsa '. $entity->getNumber() . ' no se puede cancelar');
        }
        if ($agency != $entity->getAgency() ) {
            throw $this->createNotFoundException('Esta acción solo se puede hacer en la agencia que creó la bolsa');
        }
        
        $form = $this->createCancelForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $guides = $entity->getGuides();
            foreach ($guides as $guide) {
                $guide->setBag(null);
                $entity->removeGuide($guide);
            }
            $maincompany->setCountbags($countbag--);
            $em->remove($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('guide_list'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    */
    public function getView($table, $type) {
        $option = null;
        $maincompany = $this->getUser()->getMaincompany();
        //exit(\Doctrine\Common\Util\Debug::dump($maincompany->getFormat()));
        if ($maincompany->getFormat()) {
            $option = $maincompany->getFormat()->getGprint();
        }
        $viewoption = 'NvCargaBundle:' . $table . ':' . $type . $option . '.html.twig';
        if (!$this->get('templating')->exists($viewoption) ) {
            $viewoption = 'NvCargaBundle:' . $table . ':' . $type . '.html.twig';
        }
        return $viewoption;
    }
}
