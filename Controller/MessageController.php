<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Message;
use NvCarga\Bundle\Form\MessageType;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Message controller.
 *
 * @Route("/message")
 */
class MessageController extends Controller
{

    /**
     * Lists all Message entities.
     *
     * @Route("/index", name="message")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() //este método debería ser eliminado
    {
        $em = $this->getDoctrine()->getManager();
	$user = $this->getUser();
        $entities = $em->getRepository('NvCargaBundle:Message')->findBy(array('receiver' => $user));

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Message entity.
     *
     * @Route("/create", name="message_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Message:new.html.twig")
     */
    public function createAction(Request $request) //Todos los usuario pueden crear mensajes
    {
        $entity = new Message();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $goto = $form['receiverid']->getData(); 
            if (($user->getPobox() && $goto == 0)) {
                $role = $em->getRepository('NvCargaBundle:Role')->findOneByName('ROLE_ADMIN');
                $users = $em->getRepository('NvCargaBundle:User')->findByMaincompany($maincompany);
                $receiver = null;
                $count = 0;
                while (!$receiver && ($count < count($users))) {
                    $thisuser = $users[$count];
                    if (in_array($role,$thisuser->getRoles()) && ($thisuser->getUsername() != 'trackingpremium')) {
                        $receiver = $thisuser;
                    }
                    $count++;
                }
                if (!$receiver) {
                    throw $this->createNotFoundException('No existe hay administrador para enviar el mensaje');
                }
            } else {
                $receiver = $em->getRepository('NvCargaBundle:User')->find($goto);
                if (!$receiver) {
                    throw $this->createNotFoundException('No existe el destinatario');
                }
            }
            $entity->setCreationdate(new \DateTime());
            $entity->setSender($user);
            $entity->setReceiver($receiver);
            $entity->setIsread(false);
           
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('message'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Message entity.
     *
     * @param Message $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Message $entity)
    {
        $form = $this->createForm(new MessageType(), $entity, array(
            'action' => $this->generateUrl('message_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Enviar'));

        return $form;
    }
   /**
     * Creates a form to create a Message entity.
     *
     * @param Message $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createReplyForm(Message $entity, $id)
    {
        $form = $this->createForm(new MessageType(), $entity, array(
            'action' => $this->generateUrl('message_replycreate', array('id'=>$id)),
            'method' => 'POST',
        ));
	
        $form->remove('receiverid');
        $form->remove('receivername');
        $form->remove('goto');
        $receiver = $entity->getReceiver();
        if ($receiver->getUsername() == $receiver->getEmail()) {
            $email = ' (' . $receiver->getEmail() . ')';
        } else {
            $email = ' (' . $receiver->getUsername() . ':' . $receiver->getEmail() . ')';
        }
        $receivername = $receiver->getName() . ' ' .  $receiver->getLastname() . $email;
        $form->add('submit', 'submit', array('label' => 'Enviar'));
        $form->add('receiverid', 'hidden', array('mapped'=> false, 'read_only'=> true, 'data' => $receiver->getId()));
        $form->add('receivername', 'text', array('mapped'=> false, 'read_only' => true, 'label' => 'Destinatario ', 
                                'data' => $receivername));
        //$form->add('goto', 'text', array('label' => 'Destinatario: ', 'mapped'=> false, 'required'=>true, 'data' => $entity->getSender()->getUsername(), 'read_only'=>true ));
        return $form;
    }

    /**
     * Displays a form to create a new Message entity.
     *
     * @Route("/new", name="message_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction() //Todos los usuarios pueden generar un nuevo mensaje
    {
        $entity = new Message();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Displays a form to create a new Message entity.
     *
     * @Route("/{id}/reply", name="message_reply")
     * @Template("NvCargaBundle:Message:reply.html.twig")
     */
    public function replyAction($id) //Todos los usuarios pueden generar un nuevo mensaje
    {
        $entity = new Message();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $mensaje = $em->getRepository('NvCargaBundle:Message')->findOneBy(array('id'=>$id, 'receiver' => $user));
	
        if (!$mensaje) {
            throw $this->createNotFoundException('El mensaje no existe');
        }
        $entity->setSubject('Re: '. $mensaje->getSubject());
        $entity->setSender($user);
        $entity->setReceiver($mensaje->getSender());
        $oldbody = wordwrap($mensaje->getBody(), 80, "\r\n>");
        $head = ' ' . "\r\n" . 'El '. $mensaje->getCreationdate()->format('Y-m-d H:i:s') . ' el usuario ' . $mensaje->getSender() . ' le escribió:' . "\r\n";
        $entity->setBody($head . '>'. $oldbody);
        $form   = $this->createReplyForm($entity, $id);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Displays a form to create a new Message entity.
     *
     * @Route("/{id}/replycreate", name="message_replycreate")
     * @Template("NvCargaBundle:Message:reply.html.twig")
     */
    public function replycreateAction(Request $request, $id) //Todos los usuarios pueden generar un nuevo mensaje
    {
        $entity = new Message();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $mensaje = $em->getRepository('NvCargaBundle:Message')->findOneBy(array('id'=>$id, 'receiver' => $user));
	
        if (!$mensaje) {
            throw $this->createNotFoundException('El mensaje no existe');
        }
        $entity->setSubject('Re: '. $mensaje->getSubject());
        $entity->setSender($user);
        $entity->setReceiver($mensaje->getSender());
        $oldbody = wordwrap($mensaje->getBody(), 80, "\r\n>");
        $head = ' ' . "\r\n" . 'El '. $mensaje->getCreationdate()->format('Y-m-d H:i:s') . ' el usuario ' . $mensaje->getSender() . ' le escribió:' . "\r\n";
        $entity->setBody($head . '>'. $oldbody);
        $form   = $this->createReplyForm($entity, $id);
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setCreationdate(new \DateTime());
            $entity->setIsread(false);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('message'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Message entity.
     *
     * @Route("/{id}/show", name="message_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) //Se debe counstruir un voters para mostrar los mensajes 
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $entity = $em->getRepository('NvCargaBundle:Message')->findOneBy(array('id'=>$id, 'receiver' => $user));
	
        if (!$entity) {
            throw $this->createNotFoundException('El mensaje no existe');
        }
        $entity->setIsread(true);
        $em->flush();
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Message entity.
     *
     * @Route("/{id}/delete", name="message_delete")
     */
    public function deleteAction(Request $request, $id) // También necesita un voter
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $entity = $em->getRepository('NvCargaBundle:Message')->findOneBy(array('id'=>$id, 'receiver' => $user));

            if (!$entity) {
                throw $this->createNotFoundException('No existe el mensaje a eliminar');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('message'));
    }

    /**
     * Creates a form to delete a Message entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('message_delete', array('id' => $id)))
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
    
    /**
     * Displays a form to create a new Message entity.
     *
     * @Route("/email", name="email_new")
     * @Template("NvCargaBundle:Message:email.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function emailAction(Request $request) //Todos los usuarios pueden generar un nuevo mensaje
    {
        $user = $this->getUser();
        if ($user->getUsername() != 'trackingpremium') {
            throw $this->createNotFoundException('Operación no permitida');
        }
        $defaultData = array('message' => 'Type your message here');
        $form = $this->createFormBuilder($defaultData)
            ->add('email','email', array('label' => 'Destinatario ', 'constraints' => array(
                    new NotBlank(["message" => "Debe asignar un email"]),
                    new Email(["message" => "El email '{{ value }}' no es válido.", 
                        "checkMX" => true, "checkHost" => true]))))
            ->add('subject', 'text', array('label' => 'Asunto ', 'constraints' => array(
                    new NotBlank(["message" => "Debe asignar un asunto"]))))
            ->add('body', 'textarea', array('label' => 'Mensaje ', 'constraints' => array(
                    new NotBlank(["message" => "Debe asignar un mensaje"]))))
            ->add('send', 'submit', array('label' => 'Enviar'))
            ->getForm();

        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();
            $maincompany = $this->getUser()->getMaincompany();
            $setfrom =  $maincompany->getEmail(); 
            $fromname = 'Notificación de ' . $maincompany->getName(); 
            $body = $data['body'];
            // PRUEBA DE ENVIO DE BOTON
            /*
            if ($maincompany->getBillurl()) {
                $maincompany = $this->getUser()->getMaincompany();
                $button = '/n <html>' .
                ' <head></head>' .
                ' <body>' .
                ' <center>' .
                '<table> <tr> ' .
                ' <td style="background-color: #4ecdc4;border-color: #4c5764;border: 2px solid #45b7af;padding: 10px;text-align: center;"> '. 
                ' <a style="display: block;color: #ffffff;font-size: 12px;text-decoration: none;text-transform: uppercase;" href="' . $maincompany->getBillurl().  '">' .
                ' PAGAR </a> </td> </tr> </table> . ' .
                ' </center>' . 
                ' </body>' .
                '</html>';
                $body = $body . $button;
            }
            */
            $message = \Swift_Message::newInstance()
                ->setFrom(array($setfrom => $fromname))
                ->setContentType("text/html")
                ->setSubject($data['subject'])
                ->setTo($data['email'])
                ->setBody($body);
            $send = $this->container->get('mailer')->send($message);
            return $this->redirect($this->generateUrl('homepage'));
        }
        
        return array(
            'form'   => $form->createView(),
        );
    }
    
}
