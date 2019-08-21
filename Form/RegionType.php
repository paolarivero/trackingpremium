<?php
namespace NvCarga\Bundle\Form;

use Doctrine\ORM\EntityManager;
use NvCarga\Bundle\Entity\Country;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Count;

class RegionType extends AbstractType {

    protected $em;
    protected $expanded;
    protected $user;
    function __construct(EntityManager $em, $user)
    {
        $this->em = $em;
        $this->user = $user;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Nombre de la Ciudad
        $builder->add('name', 'text',  array('label'=> 'Nombre '));
        //$builder->add('allcities', 'checkbox',  array('label'=> 'Todas las ciudades del país ', 'mapped'=>false, 'required'=>false));
        // Add listeners
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
        
    }
    protected function addElements(FormInterface $form, Country $country = null) {
        // Remove the submit button, we will place this at the end of the form later
 //       $submit = $form->get('save');
//        $form->remove('save');
        // Add the country element
        $form->add('country', 'entity', array('label'=> 'País ',
            'data' => $country,
            'empty_value' => '-- Escoja el País --',
            'class' => 'NvCargaBundle:Country',
            'choices' => $this->user->getMaincompany()->getCountries()->toarray())
        );
        // States are empty, unless we actually supplied a country
        $states = array();
        if ($country) {
            // Fetch the states from specified country
            $repo = $this->em->getRepository('NvCargaBundle:State');
            $states = $repo->findByCountry($country, array('name' => 'asc'));
        }
        // Add the state element
        $form->add('state', 'entity', array('label'=> 'Estados ', 'empty_value' => '-- Seleccione primero el País --',
            					'class' => 'NvCargaBundle:State',
            					'choices' => $states,
						'multiple' => true, 
						'mapped' => false));
        // Add submit button again, this time, it's back at the end of the form
 //       $form->add($submit);
        $cities = array();
	if ($states) {
	   $repo = $this->em->getRepository('NvCargaBundle:City');
           $cities = $repo->findByState($states, array('name' => 'asc'));
	}
        $form->add('region_cities', 'entity', array('label'=> 'Ciudades ', 'empty_value' => '-- Seleccione primero los Estados --',
            					'class' => 'NvCargaBundle:City',
            					'choices' => $cities,
						'multiple' => true,
                        ));	
    }
    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();
        // Note that the data is not yet hydrated into the entity.
        $country = $this->em->getRepository('NvCargaBundle:Country')->find($data['country']);
        $this->addElements($form, $country);
    }
    function onPreSetData(FormEvent $event) {
        $region = $event->getData();
        $form = $event->getForm();
        // We might have an empty account (when we insert a new account, for instance)
//        $country = $region->getState() ? $region->getState()->getCountry() : null;
        $country = $region->getCountry();
        $this->addElements($form, $country);
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
          $resolver->setDefaults(array(
              'data_class' => 'NvCarga\Bundle\Entity\Region'
          ));
    }
    public function getName()
    {
        return "region_type";
    }
}
