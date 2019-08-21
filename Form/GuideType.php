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

class GuideType extends AbstractType {
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
        $agencies = $this->em->getRepository('NvCargaBundle:Agency')->findByMaincompany($this->user->getMaincompany());
        $paidtypes = $this->em->getRepository('NvCargaBundle:Paidtype')->createQueryBuilder('p')
                        ->where('p.active = true')
                        ->andwhere('p.deleted = false')
                        ->orderBy('p.name', 'ASC')
                        ->getQuery()
                        ->getResult();
        $builder
            ->add('disabled_sender', 'hidden', array('mapped'=>false, 'data'=>1))
            ->add('disabled_addr', 'hidden', array('mapped'=>false, 'data'=>1))
            // DATOS DEL REMITENTE
            ->add('agency', 'entity', array('label' => 'Agencia ', 'class' => 'NvCargaBundle:Agency',
                    // 'placeholder' => '--Seleccione una agencia--',
                    'choices' => $agencies,
                    'empty_data'  => null))
            ->add('id_sender', 'hidden', array('mapped'=>false, 'data'=> 0)) // SOLO SE REQUIERE ESTE ID....
            ->add('name_sender', 'text', array('attr' => array('class' => 'resizedTextbox', 'TABINDEX' => 1),
                    'label'=> 'labels.name_sender',
                    'mapped'=>false,
                    'constraints' => array(
                    new NotBlank(["message" => "El nombre del remitente no puede estar vacío"]),
                    new Length(["min" => 2, "max" => 30,
                        "minMessage" => "El nombre del remitente debe tener al menos {{ limit }} caracteres",
                        "maxMessage" => "El nombre del remitente no puede tener mas de {{ limit }} caracteres"]))))
            ->add('lastname_sender', 'text', array('attr' => array('class' => 'resizedTextbox', 'TABINDEX' => 2),
                    'label'=> 'Apellido ', 'mapped'=>false,
                    'constraints' => array(
                    new Length(["max" => 40,
                        "maxMessage" => "El apellido del remitente no puede tener mas de {{ limit }} caracteres"]))))
            ->add('email_sender', 'email', array('required'=> false, 'attr' => array('TABINDEX' => 3),
                    'label'=> 'Email ', 'mapped'=>false,
                    'constraints' => array(
                    // new NotBlank(["message" => "Debe asignar un email del remitente"]),
                    new Email(["message" => "El email '{{ value }}' no es válido.",
                    "checkMX" => false, "checkHost" => false]))))
            ->add('direccion_sender', 'textarea', array('attr' => array ('TABINDEX' => 3), 'label'=> 'Dirección ', 'mapped'=>false,
                    'constraints' => array(new NotBlank(["message" => "La dirección del remitente no puede estar vacío"]),
                    new Length(["min" => 2, "max" => 120,
                    "minMessage" => "La dirección del remitente debe tener al menos {{ limit }} caracteres1",
                    "maxMessage" => "La dirección del remitente no puede tener mas de {{ limit }} caracteres"]))))
            ->add('zip_sender','text', array('attr' => array('class' => 'resizedTextbox', 'TABINDEX' => 5),
                    'label'=> 'Zip', 'mapped'=>false, 'required' => false))
            //->add('barrio_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),
            //    'label'=> 'Sector ', 'mapped'=>false, 'required' => false))
            ->add('mobile_sender', 'text', array('attr' => array('class' => 'resizedTextbox', 'TABINDEX' => 6),
                    'label'=> 'Móvil ', 'mapped'=>false, 'required' => false))
            ->add('phone_sender', 'text', array('attr' => array('class' => 'resizedTextbox', 'TABINDEX' => 7),
                    'label'=> 'Teléfono', 'mapped'=>false, 'required' => false))
            ->add('cityid_sender', 'hidden', array('mapped'=>false, 'data'=> 0))
            ->add('cityname_sender', 'text', array('attr' => array('TABINDEX' => 8, 'class' => 'resizedTextbox'),'label'=> 'Ciudad ',
                'mapped'=>false, 'constraints' => array(new NotBlank(["message" => "Escoja una ciudad para el remitente"]))))
            /*
            ->add('state_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),'label'=> 'Estado ',
                'mapped'=>false, 'read_only' => true))
            ->add('country_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),'label'=> 'País ',
                'mapped'=>false, 'read_only' => true))
            */
            // NOTIFICACIONES DE LA GUIA
            ->add('emailnoti', 'checkbox', array('label' => 'Notificación Email ', 'attr' => array('class' => 'resizedTextbox',     'TABINDEX' => 8)))

        // DATOS DEL DESTINATARIO
            ->add('id_addr', 'hidden', array('mapped'=>false, 'data'=> 0)) // SOLO SE REQUIERE ESTE ID....
            ->add('name_addr', 'text', array('attr' => array('class' => 'resizedTextbox', 'TABINDEX' => 9),
                    'label'=> 'Nombre ', 'mapped'=>false,
                    'constraints' => array(new NotBlank(["message" => "El nombre del destinatario no puede estar vacía"]),
                    new Length(["min" => 2, "max" => 30,
                    "minMessage" => "El nombre del destinatario debe tener al menos {{ limit }} caracteres",
                    "maxMessage" => "El nombre del destinatario no puede tener mas de {{ limit }} caracteres"]))))
            ->add('lastname_addr', 'text', array('attr' => array('class' => 'resizedTextbox','TABINDEX' => 10),
                    'label'=> 'Apellido ', 'mapped'=>false, 'constraints' => array(new Length(["max" => 40,
                    "maxMessage" => "El apellido del destinatario no puede tener mas de {{ limit }} caracteres"]))))
            ->add('email_addr', 'email', array('required'=>false, 'attr' => array('TABINDEX' => 11),
                    'label'=> 'Email ', 'mapped'=>false,
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
            //'attr' => array('class' => 'resizedTextbox'),'label'=> 'Sector ',
            //    'mapped'=>false, 'required' => false))
            ->add('zip_addr','text', array('attr' => array('class' => 'resizedTextbox', 'TABINDEX' => 13),
                    'label'=> 'Zip', 'mapped'=>false, 'required' => false))
            ->add('mobile_addr', 'text', array('attr' => array('class' => 'resizedTextbox', 'TABINDEX' => 14),
                    'label'=> 'Móvil ', 'mapped'=>false, 'required' => false))
            ->add('phone_addr', 'text', array('attr' => array('class' => 'resizedTextbox', 'TABINDEX' => 15),
                    'label'=> 'Teléfono', 'mapped'=>false, 'required' => false))
            ->add('cityid_addr', 'hidden', array('mapped'=>false, 'data'=> 0))
            ->add('cityname_addr', 'text', array('attr' => array('TABINDEX' => 16, 'class' => 'resizedTextbox'),
                    'label'=> 'Ciudad ', 'mapped'=>false,
                    'constraints' => array(new NotBlank(["message" => "Escoja una ciudad para el destinatario"]))))
            /*
            ->add('state_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),'label'=> 'Estado ',
                'mapped'=>false, 'read_only' => true))
            ->add('country_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),'label'=> 'País ',
                'mapped'=>false, 'read_only' => true))
            */
            ->add('mobilnoti', 'checkbox', array('label' => 'Notificación móvil ', 'required'=>false,
                    'attr' => array('class' =>'resizedTextbox', 'TABINDEX' => 17)))
            ->add('fvround', 'hidden', array('mapped'=>false, 'data' => 0))
            //->add('tracking', 'text', array('label' => 'Tracking ')) // SE ASIGNA AUTOMATICAMENTE
            ->add('contain', 'textarea', array('label' => 'Contenido ', 'attr' => array('TABINDEX' => 19, 'placeholder' => 'Contenido')))
            //->add('tracking', 'text', array('label' => 'Tracking ',  'required'=> false,
            //      'attr' => array('style' => 'width: 20em')))
            ->add('realweight', 'number', array('label' => 'Peso real ', 'scale' => 2,
                    'attr'=> array('TABINDEX' => 20, 'placeholder' => 'Peso') ))
            ->add('pieces', 'integer', array('label' => 'Piezas ', 'empty_data' => 1,
                    'attr'=> array('TABINDEX' => 21, 'placeholder' => 'Piezas')))
            ->add('cod', 'entity', array('label' => 'Tipo COD ', 'class' => 'NvCargaBundle:COD'))
            ->add('paidtype', 'entity', array('label' => 'Tipo Pago ',
                    'class' => 'NvCargaBundle:Paidtype',
                    'choices' => $paidtypes, ))
            ->add('declared', 'number', array('label' => 'Monto Declarado ', 'attr'=> array('placeholder' => 'Monto declarado')))
            ->add('tax_per', 'number', array('label' => '% tax ',  'scale' => 2,))
            ->add('file', null , array('label'=> 'Imagen ', 'required' => false))
            //->add('ordernumber', 'text', array('label' => 'Número de orden ', 'required' => false,
            //      'attr' => array('style' => 'width: 25em')))
            ->add('tariffid', 'hidden', array('mapped' => false))
            ->add('tariffname', 'text', array('label' => false, 'required' => false, 'mapped' => false,
                    'read_only'=>true,      'constraints' => array(
                    new NotBlank(["message" => "Debe tener una tarifa"])),
                    'attr' => array('style' => 'width: 15em')))
            ->add('calculate', 'button', array('label' => 'Calcular'))
            ->add('paidweight', 'number', array('scale' => 2, 'required' => false, 'label' => 'Peso Dim ',
                    'read_only' => true,))
            ->add('downpayment', 'number', array('label' => 'Pago inicial'))
            ->add('insurance_amount', 'number', array('label'=>'Monto asegurado ',
                'attr'=> array('placeholder' => 'Monto asegurado')))
            ->add('measurevalue', 'number', array('label' => 'Valor medida ', 'scale' => 2,  )) // Viene de la tarifa
            ->add('insurance_per', 'number', array('label' => '% seg ' ))
            ->add('tax_paid', 'number', array('label'=> false, 'read_only' => true, 'scale' => 2,))
            ->add('insurance_paid', 'number', array('label'=> false, 'read_only' => true, ))
            ->add('discount', 'number', array('label'=> false, 'scale' => 2, ))
            ->add('otherfees', 'number', array('label'=> false, 'scale' => 2, ))
            ->add('freight', 'number', array('label'=> false, 'read_only' => true, 'scale' => 2, ))
            ->add('volfreight', 'number', array('label'=> false, 'read_only' => true, 'scale' => 2, ))
            ->add('totalpaid', 'number', array('label'=> false, // 'read_only' => true,
                    'scale' => 2, 'rounding_mode' => 0 ,)) // Se calcula
            // PARAMETROS OCULTOS PARA TOMAR INFORMACION DE TARIFA
            // Para buscar ciudad
            //	   ->add('namecity', 'text', array('label' => 'Nombre ', 'mapped'=> false, 'required' => false))
            //	   ->add('searchcity', 'button', array('label' => 'Buscar'))
                ->add('selcity', 'hidden', array('label'=> false, 'mapped'=> false))
            // Para buscar cliente
            //	   ->add('namecustomer', 'text', array('label' => 'Nombre ', 'mapped'=> false, 'required' => false, 'attr' => array('style' => 'width: 12em')))
            //	   ->add('lastnamecustomer', 'text', array('label' => 'Apellido ', 'mapped'=> false, 'required' => false, 'attr' => array('style' => 'width: 12em')))
            //	   ->add('searchcustomer', 'button', array('label' => 'Buscar'))
                ->add('selcustomer', 'hidden', array('label'=> false, 'mapped'=> false))
            // Para agregar paquetes a la guía
            ->add('packages', 'collection', array('mapped' => true, 'required' => false, 'label'=> false, 'type' => new MinipackType(),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'prototype' => true,
                    'error_bubbling' => false,
                    'attr' => array('class' => 'list-packages','TABINDEX' => 18),
                        'options' => array('label' => false),
                    ))
            ->add('typecus_sender', 'entity', array('label' => false, 'mapped' => false,
                    'required' => false, 'empty_value' => false,
                    'data_class'=> 'NvCarga\Bundle\Entity\Customertype', 'class'=> 'NvCargaBundle:Customertype',
                    'expanded' => true, 'multiple'=>false, ))
            ->add('typecus_addr', 'entity', array('label' => false, 'mapped' => false,
                    'required' => false, 'empty_value' => false,
                    'data_class'=> 'NvCarga\Bundle\Entity\Customertype', 'class'=> 'NvCargaBundle:Customertype',
                    'expanded' => true, 'multiple'=> false, ))
            ->add('edition', 'hidden', array('label'=> false, 'mapped'=> false))
            ->add('clock', 'hidden', array('mapped'=>false))

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
        $shipper = $data->getSender();
        $receiver = $data->getAddressee();
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
            'data_class' => 'NvCarga\Bundle\Entity\Guide'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'guide_type';
    }
}
