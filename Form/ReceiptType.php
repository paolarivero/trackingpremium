<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use NvCarga\Bundle\Entity\Country;
use NvCarga\Bundle\Entity\State;


class ReceiptType extends AbstractType
{
    protected $em;
    protected $user;
    function __construct(EntityManager $em, $user)
    {
        $this->em = $em;
        $this->user = $user;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fecha =  new \Datetime();
        $carriers = $this->em->getRepository('NvCargaBundle:Carrier')->findBy(['maincompany'=>$this->user->getMaincompany(), 'active'=>true]);
        $builder
            // REMITENTE
            ->add('disabled_sender', 'hidden', array('mapped'=>false, 'data'=>1))
            ->add('disabled_addr', 'hidden', array('mapped'=>false, 'data'=>1))
            ->add('id_sender', 'hidden', array('mapped'=>false, 'data'=> 0)) // SOLO SE REQUIERE ESTE ID....
            ->add('name_sender', 'text', array('attr' => array( 'TABINDEX' => 1), 
                    'label'=> 'labels.name_sender',
                    'mapped'=>false, 
                    'constraints' => array(
                    new NotBlank(["message" => "El nombre del remitente no puede estar vacío"]), 
                    new Length(["min" => 2, "max" => 30,
                        "minMessage" => "El nombre del remitente debe tener al menos {{ limit }} caracteres",
                        "maxMessage" => "El nombre del remitente no puede tener mas de {{ limit }} caracteres"]))))
            ->add('lastname_sender', 'text', array('attr' => array( 'TABINDEX' => 2), 
                    'label'=> 'Apellido ', 'mapped'=>false,
                    'constraints' => array( 
                    new Length(["max" => 40,
                        "maxMessage" => "El apellido del remitente no puede tener mas de {{ limit }} caracteres"]))))
            ->add('email_sender', 'email', array('required'=> false, 'attr' => array('TABINDEX' => 3),'label'=> 'Email ', 'mapped'=>false,
                    'constraints' => array(
                    // new NotBlank(["message" => "Debe asignar un email del remitente"]),
                    new Email(["message" => "El email '{{ value }}' no es válido.", 
                        "checkMX" => false, "checkHost" => false]))))
            ->add('direccion_sender', 'textarea', array('attr' => array ('TABINDEX' => 3), 'label'=> 'Dirección ', 'mapped'=>false, 
                'constraints' => array(new NotBlank(["message" => "La dirección del remitente no puede estar vacío"]), 
                    new Length(["min" => 2, "max" => 120,
                        "minMessage" => "La dirección del remitente debe tener al menos {{ limit }} caracteres",
                        "maxMessage" => "La dirección del remitente no puede tener mas de {{ limit }} caracteres"]))))
            ->add('zip_sender','text', array('attr' => array( 'TABINDEX' => 5), 
                'label'=> 'Zip', 'mapped'=>false, 'required' => false))
            //->add('barrio_sender', 'text', array(
            //    'label'=> 'Sector ', 'mapped'=>false, 'required' => false))
            ->add('mobile_sender', 'text', array('attr' => array( 'TABINDEX' => 6),
                'label'=> 'Móvil ', 'mapped'=>false, 'required' => false))
            ->add('phone_sender', 'text', array('attr' => array( 'TABINDEX' => 7),
                'label'=> 'Teléfono', 'mapped'=>false, 'required' => false))
            ->add('noti_sender', 'checkbox', array('label' => 'Notificación Email ', 'required'=> false, 
                'mapped'=>false, 'data'=>false, 'attr' => array('class' => 'resizedTextbox')))
            ->add('cityid_sender', 'hidden', array('mapped'=>false, 'data'=> 0))
            ->add('cityname_sender', 'text', array('attr' => array('TABINDEX' => 8), 'label'=> 'Ciudad ',
                'mapped'=>false, 'constraints' => array(new NotBlank(["message" => "Escoja una ciudad para el remitente"]))))
            ->add('typecus_sender', 'entity', array('label' => false, 'mapped' => false, 
                'required' => false, 'empty_value' => false,
                'data_class'=> 'NvCarga\Bundle\Entity\Customertype', 'class'=> 'NvCargaBundle:Customertype', 
                'expanded' => true, 'multiple'=>false, 'label_attr' => array('class' => 'radio-inline', 
                'attr' => array('class'=>'icheck'))))
                
             // DATOS DEL DESTINATARIO
            ->add('id_addr', 'hidden', array('mapped'=>false, 'data'=> 0)) // SOLO SE REQUIERE ESTE ID....
            ->add('name_addr', 'text', array('attr' => array( 'TABINDEX' => 9),
                'label'=> 'Nombre ', 'mapped'=>false,
                'constraints' => array(new NotBlank(["message" => "El nombre del destinatario no puede estar vacía"]), 
                    new Length(["min" => 2, "max" => 30, 
                        "minMessage" => "El nombre del destinatario debe tener al menos {{ limit }} caracteres",
                        "maxMessage" => "El nombre del destinatario no puede tener mas de {{ limit }} caracteres"]))))
            ->add('lastname_addr', 'text', array('attr' => array('TABINDEX' => 10),
                'label'=> 'Apellido ', 'mapped'=>false, 'constraints' => array(new Length(["max" => 40,
                        "maxMessage" => "El apellido del destinatario no puede tener mas de {{ limit }} caracteres"]))))
            ->add('email_addr', 'email', array('attr' => array('TABINDEX' => 11), 'required'=>false, 'label'=> 'Email ', 'mapped'=>false,
                'constraints' => array(
                // new NotBlank(["message" => "Debe asignar un email del destinatario"]),
                new Email(["message" => "El email '{{ value }}' no es válido.", 
                    "checkMX" => false, "checkHost" => false]))))
            ->add('direccion_addr', 'textarea', array('attr' => array('TABINDEX' => 12),'label'=> 'Dirección ', 'mapped'=>false, 
                'constraints' => array(
                new NotBlank(["message" => "La dirección del destinatario no puede estar vacía"]), 
                new Length(["min" => 2, "max" => 120,
                    "minMessage" => "La dirección del destinatario debe tener al menos {{ limit }} caracteres",
                    "maxMessage" => "La dirección del destinatario no puede tener mas de {{ limit }} caracteres"]))))
            ->add('barrio_addr', 'hidden', array('mapped'=>false))
            //'label'=> 'Sector ', 
            //    'mapped'=>false, 'required' => false))
            ->add('zip_addr','text', array('attr' => array('TABINDEX' => 13),
                'label'=> 'Zip', 'mapped'=>false, 'required' => false))
            ->add('mobile_addr', 'text', array('attr' => array( 'TABINDEX' => 14),
                'label'=> 'Móvil ', 'mapped'=>false, 'required' => false))
            ->add('phone_addr', 'text', array('attr' => array( 'TABINDEX' => 15),
                'label'=> 'Teléfono', 'mapped'=>false, 'required' => false))
             ->add('noti_addr', 'checkbox', array('label' => 'Notificación Email ', 'required'=> false,  
                'mapped'=>false, 'data'=>false, 'attr' => array('class' => 'resizedTextbox')))
            ->add('cityid_addr', 'hidden', array('mapped'=>false, 'data'=> 0))
            ->add('cityname_addr', 'text', array('attr' => array('TABINDEX' => 16), 'label'=> 'Ciudad ', 
                'mapped'=>false, 'constraints' => array(new NotBlank(["message" => "Escoja una ciudad para el destinatario"]))))
            ->add('typecus_addr', 'entity', array('label' => false, 'mapped' => false, 
                    'required' => false, 'empty_value' => false,
                    'data_class'=> 'NvCarga\Bundle\Entity\Customertype', 'class'=> 'NvCargaBundle:Customertype', 
                    'expanded' => true, 'multiple'=>false, 'label_attr' => array('class' => 'radio-inline')))
                    
            // NOTA
            ->add('note','textarea', array('label'=> 'Nota ', 'required' => false, 'attr' => array('TABINDEX' => 17)))
            
            // DATOS DEL PAQUETE PRINCIPAL
            ->add('carrier', 'entity', array('label'=>'Carrier ', 'required' => true, 
            'class'=>'NvCargaBundle:Carrier', 'choices'=>$carriers, ))
            ->add('tracking','text', array('label'=> 'Tracking ', 'required' => false, 'attr' => array('placeholder' => 'Tracking')))
            ->add('weight','number', array('label'=>'Peso/Lb ', 'required' => true, 'scale' => 2, 
                'attr' => array('placeholder'=>'Peso/Libras ')))
            ->add('height','number', array('label'=>'Alto "','required' => true, 'scale' => 2, 
                'attr' => array('placeholder'=>'Alto ')))
            ->add('width','number', array('label'=>'Ancho "','required' => true, 'scale' => 2, 
                'attr' => array('placeholder'=>'Ancho', )))
            ->add('length','number', array('label'=>'Largo "', 'required' => true, 'scale' => 2, 
                'attr' => array('placeholder'=> 'Largo', )))
            ->add('description','text', array('label'=>'Descripción ', 'required' => false, 
                'attr' => array('placeholder'=>'Descripción')))
            ->add('arrivedate', 'text', array('label' => 'Fecha ',))
            ->add('value','number', array('label'=>'Valor ', 'required' => true, 'scale' => 2, 
                'attr' => array('placeholder' => 'Valor')))
            ->add('npack', null, array('label' => 'Bultos', 'required'=>true, 'attr' => array('placeholder'=>'Bultos ')))
            ->add('packtype', 'choice', array('label' => 'Tipo ',
                        'choices' => array('Caja' => 'Caja', 
                        'Sobre' => 'Sobre', 'Bulto' => 'Bulto'),'choices_as_values' => true,))
            //->add('reference','textarea', array('label'=>false, 'required' => false, 
            //    'attr' => array('placeholder'=>'Referencia')))
            //->add('quantity','integer', array('label'=>false,'empty_data' => '1', 'required' => true, 
            //    'attr' => array('placeholder' => 'Cantidad')))
            
            // LISTA DE PAQUETES ADICIONALES
//             ->add('packages', 'collection', array('mapped' => true, 'required' => false, 'label' => false, 
//                 'type' => new PackageType(), 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false,
//                 'prototype' => true,  'error_bubbling' => false, 'attr' => array('class' => 'list-packages'),
//                 'options' => array('label'=> false)
//             ))
            // Para buscar ciudad
            ->add('selcity', 'hidden', array('label'=> false, 'mapped'=> false))
            // Para buscar cliente
            ->add('selcustomer', 'hidden', array('label'=> false, 'mapped'=> false))
            ->add('edition', 'hidden', array('label'=> false, 'mapped'=> false))
        ; 
            $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
            $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    protected function addElements(FormInterface $form, Country $country1 = null, Country $country2 = null) {
        $countries = $this->user->getMaincompany()->getCountries()->toarray();

        $form->add('country_sender', 'entity', array('label'=> 'País ',
                    'placeholder' => '-- Escoja el País --',
                    'class' => 'NvCargaBundle:Country', 
                    'mapped' => false,
                    'constraints' => array(new NotNull(["message" => "Escoja el país para el remitente"])),
                    'choices' => $countries,
                    ));
        
        $form->add('country_addr', 'entity', array('label'=> 'País ',
                    'placeholder' => '-- Escoja el País --',
                    'class' => 'NvCargaBundle:Country', 
                    'mapped' => false,
                    'constraints' => array(new NotNull(["message" => "Escoja el país para el destinatario"])),
                    'choices' => $countries,
                    ));
        
        // States are empty, unless we actually supplied a country
        $states1 = array();
        if ($country1) {
            // Fetch the states from specified country
            $repo = $this->em->getRepository('NvCargaBundle:State');
            $states1 = $repo->findByCountry($country1, array('name' => 'asc'));
        }
        $states2 = array();
        if ($country2) {
            // Fetch the states from specified country
            $repo = $this->em->getRepository('NvCargaBundle:State');
            $states2 = $repo->findByCountry($country2, array('name' => 'asc'));
        }
        // Add the state element
        $form->add('state_sender', 'entity', array('label'=> 'Estado ',
                'placeholder' => '--Selecciona primero el País--', 
                'class' => 'NvCargaBundle:State',
                'choices' => $states1,
                'mapped' => false,
                'constraints' => array(new NotNull(["message" => "Escoja el estado/provincia para el remitente"]),
                )));
        $form->add('state_addr', 'entity', array('label'=> 'Estado ',
                'placeholder' => '--Selecciona primero el País--',
                'class' => 'NvCargaBundle:State',
                'choices' => $states2,
                'mapped' => false,
                'constraints' => array(new NotNull(["message" => "Escoja el estado/provincia para el destinatario"]),
                )));
    }
    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();
        // Note that the data is not yet hydrated into the entity.
        $country1 = $this->em->getRepository('NvCargaBundle:Country')->find($data['country_sender']);
        $country2 = $this->em->getRepository('NvCargaBundle:Country')->find($data['country_addr']);
        $this->addElements($form, $country1, $country2);
    }
    function onPreSetData(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();
        $shipper = $data->getShipper();
        $receiver = $data->getReceiver();
        if ($shipper) {
            $country1 = $shipper->getAdrdefault()->getCity()->getState()->getCountry();
        } else {
            $country1 = null;
        }
        if ($receiver) {
            $country2 = $receiver->getCity()->getState()->getCountry();
        } else {
            $country2 = null;
        }
        // We might have an empty account (when we insert a new account, for instance)
        //$country = $city->getState() ? $city->getState()->getCountry() : null;
        $this->addElements($form, $country1,$country2);
    }
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
       $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Receipt'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'receipt_type';
    }
 
}
