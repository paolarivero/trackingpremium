<?php

namespace NvCarga\Bundle\Form;

use Doctrine\ORM\EntityManager;
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

class MaincompanyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dimfactor = array(139=>139,166=>166);
        $volumens = array(1728=>1728,1000000=>1000000);
        
        $builder
            ->add('name', null, array('label'=> 'Nombre '))
            ->add('acronym', null, array('label'=> 'Acrónimo '))
            ->add('dimfactor', 'choice', array('attr'=>array('title'=>'En nortemamerica es comunmente usado  in3/lb = 166, el internacional es [in3/lb] = 139'), 'label'=> 'Factor dimensional ', 'choices'=>$dimfactor, 'multiple'=>false))
            ->add('url', null , array('label'=> 'URL de la Empresa'))
            ->add('billurl', null , array('label'=> 'URL para procesar pagos'))
            ->add('homepage', null , array('label'=> 'Homepage del sistema '))
            ->add('poboxmsg', 'textarea' , array('label'=> 'Mensaje a clientes  ', 'attr' => array('rows' => '10'),))
            ->add('email', null , array('label'=> 'Email '))
            ->add('roundweight', 'choice', array('label'=> 'Por peso ', 'choices' => array('Ninguno' => 'Ninguno', 'Individual' => 'Individual', 'Total'=>'Total'), 'choices_as_values' => true))
            ->add('roundvol', 'choice', array('label'=> 'Por volumen ', 'choices' => array('Ninguno' => 'Ninguno', 'Individual' => 'Individual', 'Total'=>'Total'), 'choices_as_values' => true))
            ->add('roundtotal', null , array('label'=> 'Redondeo total ', 'attr' => array('class' => 'icheck')))
            ->add('ininum', null , array('label'=> 'Numeración inicial de casilleros ', 'required' => true))
            ->add('iniguide', null , array('label'=> 'labels.iniguide','required' => true))
            ->add('prefixguide', 'text' , array('label'=> 'labels.prefixguides'))
            ->add('prefixpobox', 'text' , array('label'=> 'Prefijo de Casilleros '))
            ->add('prefixconsol', 'text' , array('label'=> 'labels.prefixconsols', ))
            ->add('convertvol', 'choice' , array('label'=> 'Factor para calcular volumen ', 'attr'=>array('title'=>'Para medidas en pulgadas el factor será 12*12*12 (1728) y para medidas en centímetros el factor será 100*100*100 (1000000)'), 'choices'=>$volumens, 'multiple'=>false))
            ->add('customername', null, array('label'=> 'Nombre de cliente ', 'attr' => array('class' => 'icheck')))
            ->add('companyname', null, array('label'=> 'Nombre de Empresa ', 'attr' => array('class' => 'icheck')))
            ->add('numbername', null, array('label'=> 'Número de Casillero ', 'attr' => array('class' => 'icheck')))
            ->add('firststatus', 'checkbox', array('label'=>'Asignar primer status automáticamente ', 'attr'=>array('class'=>'icheck')))
            ->add('showalltariffs', 'checkbox', array('label'=>'Mostrar todas las tarifas al crear guía ', 'attr'=>array('class'=>'icheck')))
        ;
        /*
            $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
            $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
            */
    }
    /*
    protected function addElements(FormInterface $form, Country $country = null) {
        // Remove the submit button, we will place this at the end of the form later
 //       $submit = $form->get('save');
//        $form->remove('save');
        // Add the country element
        
        $form->add('country', 'entity', array('label'=> 'País ',
            'data' => $country,
            'empty_value' => '-- Escoja el País --',
            'class' => 'NvCargaBundle:Country',
            'mapped' => false,
            'constraints' => array(new NotNull(["message" => "Escoja el país para la agencia"])),
            ));
        // States are empty, unless we actually supplied a country
        $states = array();
        if ($country) {
            // Fetch the states from specified country
            $repo = $this->em->getRepository('NvCargaBundle:State');
            $states = $repo->findByCountry($country, array('name' => 'asc'));
        }
        // Add the state element
        $form->add('state', 'entity', array('label'=> 'Estado ',
            'empty_value' => '-- Seleccione primero el País --',
            'class' => 'NvCargaBundle:State',
            'choices' => $states,
            'mapped' => false,
            'constraints' => array(new NotNull(["message" => "Escoja el estado/provincia para la agencia"])),
        ));
        // Add submit button again, this time, it's back at the end of the form
 //       $form->add($submit);
    }
    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();
        // Note that the data is not yet hydrated into the entity.
        $country = $this->em->getRepository('NvCargaBundle:Country')->find($data['country']);
        $this->addElements($form, $country);
    }
    function onPreSetData(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();
        // We might have an empty account (when we insert a new account, for instance)
        $list = $data->getCountries()->toArray();
        $country = null;
        if ($list) {
            $country = $list[0];
        }
        // $country = $city->getState() ? $city->getState()->getCountry() : null;
        $this->addElements($form, $country);
    }
    */
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Maincompany'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'maincompany_type';
    }
}
