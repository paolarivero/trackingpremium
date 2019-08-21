<?php

namespace NvCarga\Bundle\Form;

use Doctrine\ORM\EntityManager;
use NvCarga\Bundle\Entity\Warehouse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

use NvCarga\Bundle\Entity\Country;
use NvCarga\Bundle\Entity\State;

class PoboxType extends AbstractType
{
    protected $em;
    protected $maincompany;
    function __construct(EntityManager $em, $maincompany)
    {
        $this->em = $em;
        $this->maincompany = $maincompany;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
            ->add('id_customer', 'hidden', array('mapped'=>false, 'data'=> 0)) // SOLO SE REQUIERE ESTE ID....
            ->add('name_customer', 'text', array('label'=> 'Nombre ', 'mapped'=>false, 
                'attr'=>['placeholder' =>'Primer nombre'],
                'constraints' => array(
                new NotBlank(["message" => "El nombre no puede estar vacío"]), 
                new Length(["min" => 2, "max" => 30,
                            "minMessage" => "El nombre debe tener al menos {{ limit }} caracteres",
                            "maxMessage" => "El nombre no puede tener mas de {{ limit }} caracteres"]))))
            ->add('lastname_customer', 'text', array('label'=> 'Apellido ', 'mapped'=>false,
                'attr'=>['placeholder' =>'Apellido'],
                'constraints' => array(
                new Length(["max" => 40,
                            "maxMessage" => "El apellido no puede tener mas de {{ limit }} caracteres"]))))	
            /*->add('docid_customer', 'text', array('label'=> 'Documento de identidad ', 'mapped'=>false, 'required' => false,
                'constraints' => array(
                // new NotBlank(["message" => "Debe suministrar un documento de identidad"]), 
                new Length(["min" => 5, "max" => 30,
                            "minMessage" => "El documento de identidad debe tener al menos {{ limit }} caracteres",
                            "maxMessage" => "El documento de identidad no puede tener mas de {{ limit }} caracteres"]))))*/				
            ->add('address_customer', 'textarea', array('label'=> 'Dirección ', 'mapped'=>false, 
                'attr'=>['placeholder' =>'Dirección actual'],
                'constraints' => array(
                new NotBlank(["message" => "La dirección no puede estar vacía"]), 
                new Length(["min" => 2, "max" => 120,
                            "minMessage" => "La dirección debe tener al menos {{ limit }} caracteres",
                            "maxMessage" => "La dirección no puede tener mas de {{ limit }} caracteres"]))))
            ->add('phone_customer', 'text', array('label'=> 'Teléfono', 'mapped'=>false, 
                'attr'=>['placeholder' =>'Número de casa'],
                'required' => false))
            ->add('mobile_customer', 'text', array('label'=> 'Móvil ', 'mapped'=>false,
                'attr'=>['placeholder' =>'Número móvil'],
                'required' => false))
            // ->add('barrio_customer', 'text', array('label'=> 'Sector ', 'mapped'=>false, 'required' => false))
            ->add('email_customer', 'repeated', array('type' => 'email', 'mapped'=>false, 
                'first_options'=>array('label'=> 'Email ', 'attr'=>['placeholder' =>'Correo electrónico'] ),
                'second_options' => array('label' => 'Repita el Email ', 'attr'=>['placeholder' =>'Repita Correo electrónico'] ),
                'invalid_message' => 'Los emails deben ser iguales',
                'constraints' => array(new NotBlank(["message" => "Debe asignar un email"]),
                    new Email(["message" => "El email '{{ value }}' no es válido.", 
                                "checkMX" => true, 
                                "checkHost" => true]) 
                )))
            ->add('cityid_customer', 'hidden', array('mapped'=>false))
            ->add('cityname_customer', 'text', array('label'=> 'Ciudad ', 'attr' => array('title' => 'Asigne el nombre si no encuentra la ciudad'),
                'mapped'=>false, 'constraints' => array(new NotBlank(["message" => "Escoja una ciudad para el cliente"])))) 
            //->add('cityname_customer', 'text', array('label'=> 'Ciudad ', 'mapped'=>false, 'read_only' => true, 
            //        'constraints' => array(new NotBlank(["message" => "Escoja una ciudad"]))))  
            // ->add('state_customer', 'text', array('label'=> 'Estado ', 'mapped'=>false, 'read_only' => true))
            // ->add('country_customer', 'text', array('label'=> 'País ', 'mapped'=>false, 'read_only' => true)) 
            ->add('zip_customer','text', array('label'=> 'Zip', 'mapped'=>false, 'required' => false))	
            ->add('password', 'repeated', array('type' => 'password', 
                    'constraints' => array(new NotBlank(["message" => "Debe colocar un password"])),
                    'first_options'  => array('label' => 'Password', 'attr'=>['placeholder' =>'Crea una contraseña'] ),
                    'second_options' => array('label' => 'Repita el Password', 'attr'=>['placeholder' =>'Repita la contraseña'] ), 
                    'invalid_message' => 'Los password deben ser iguales'))
            ->add('type', 'entity', array('label' => false, 'mapped' => false, 
                'required' => false, 'empty_value' => false,
                'data_class'=> 'NvCarga\Bundle\Entity\Customertype', 'class'=> 'NvCargaBundle:Customertype', 
                'expanded' => true, 'multiple'=>false,))
        ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }
    protected function addElements(FormInterface $form, Country $country1 = null) {
        $countries = $this->maincompany->getCountries()->toarray();

        $form->add('country_customer', 'entity', array('label'=> 'País ',
                    'placeholder' => '-- Escoja el País --',
                    'class' => 'NvCargaBundle:Country', 
                    'mapped' => false,
                    'constraints' => array(new NotNull(["message" => "Escoja el país "])),
                    'choices' => $countries,
                    ));
                    
        // States are empty, unless we actually supplied a country
        $states1 = array();
        if ($country1) {
            // Fetch the states from specified country
            $repo = $this->em->getRepository('NvCargaBundle:State');
            $states1 = $repo->findByCountry($country1, array('name' => 'asc'));
        }
        // Add the state element
        $form->add('state_customer', 'entity', array('label'=> 'Estado ',
                'placeholder' => '--Selecciona primero el País--', 
                'class' => 'NvCargaBundle:State',
                'choices' => $states1,
                'mapped' => false,
                'constraints' => array(new NotNull(["message" => "Escoja el estado/provincia "]),
                )));
    }
    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();
        // Note that the data is not yet hydrated into the entity.
        $country1 = $this->em->getRepository('NvCargaBundle:Country')->find($data['country_customer']);
        $this->addElements($form, $country1);
    }
    function onPreSetData(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();
        $customer = $data->getCustomer();
        $country1 = null;
        if ($customer) {
            if ($customer->getAdrdefault()->getCity()) {
                $country1 = $customer->getAdrdefault()->getCity()->getState()->getCountry();
            } 
        }
        // We might have an empty account (when we insert a new account, for instance)
        //$country = $city->getState() ? $city->getState()->getCountry() : null;
        $this->addElements($form, $country1);
    }
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Pobox'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pobox_type';
    }
}
