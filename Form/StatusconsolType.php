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

class StatusconsolType extends AbstractType
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
        $steps = $this->em->getRepository('NvCargaBundle:Stepstatus')->findBy(['maincompany'=>$this->maincompany]);
        $builder
            ->add('date','text', array('label' => 'Fecha '))
            ->add('step', 'entity', array('label'=>'labels.paso', 
                'class'=> 'NvCargaBundle:Stepstatus', 'multiple'=> false,
                'choices' => $steps))
            ->add('comment','text', array('label' => 'Comentario '))
            ->add('cityid_track', 'hidden', array('mapped'=>false))
            ->add('clock', 'hidden', array('mapped'=>false))
            //->add('place', null, array('mapped'=>true))
            ->add('cityname_track', 'text', array('label'=> 'Ciudad ', 
                'attr' => array('title' => 'Asigne el nombre si no encuentra la ciudad'),
                'mapped'=>false, 'constraints' => array(new NotBlank(["message" => "Escoja una ciudad"]))))
            ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }
    protected function addElements(FormInterface $form, Country $country = null) {
        $countries = $this->maincompany->getCountries()->toarray();

        $form->add('country_track', 'entity', array('label'=> 'País ',
                    'placeholder' => '-- Escoja el País --',
                    'class' => 'NvCargaBundle:Country', 
                    'mapped' => false,
                    'constraints' => array(new NotNull(["message" => "Escoja el país "])),
                    'choices' => $countries,
                    ));
                    
        // States are empty, unless we actually supplied a country
        $states = array();
        if ($country) {
            // Fetch the states from specified country
            $repo = $this->em->getRepository('NvCargaBundle:State');
            $states = $repo->findByCountry($country, array('name' => 'asc'));
        }
        // Add the state element
        $form->add('state_track', 'entity', array('label'=> 'Estado ',
                'placeholder' => '--Selecciona primero el País--', 
                'class' => 'NvCargaBundle:State',
                'choices' => $states,
                'mapped' => false,
                'constraints' => array(new NotNull(["message" => "Escoja el estado/provincia "]),
                )));
    }
    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();
        // Note that the data is not yet hydrated into the entity.
        $country = $this->em->getRepository('NvCargaBundle:Country')->find($data['country_track']);
        $this->addElements($form, $country);
    }
    function onPreSetData(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();
        $place = $data->getPlace();
        $country = null;
        if ($place) {
            $country = $place->getState()->getCountry();
        } 
        $this->addElements($form, $country);
    }
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Statusconsol'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'statusconsol_type';
    }
}
