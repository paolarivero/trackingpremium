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

class CityType extends AbstractType {

    protected $em;
    protected $maincompany;
    function __construct(EntityManager $em)
    {
        $this->em = $em;
        $server = $_SERVER['SERVER_NAME'];
        /*
        $this->maincompany = $em->getRepository("NvCargaBundle:Maincompany")->findOneByHomepage($server);
        */
        $this->maincompany = $em->getRepository("NvCargaBundle:Maincompany")->createQueryBuilder('m')
            ->where('m.homepage =:server OR m.homepage_aux =:server')
            ->setParameter('server', $server )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Nombre de la Ciudad
        $builder->add('name', 'text',  array('label'=> 'Nombre '));
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
            'mapped' => false,
            'choices'=>$this->maincompany->getCountries()->toArray(),
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
        $city = $event->getData();
        $form = $event->getForm();
        // We might have an empty account (when we insert a new account, for instance)
        $country = $city->getState() ? $city->getState()->getCountry() : null;
        $this->addElements($form, $country);
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
          $resolver->setDefaults(array(
              'data_class' => 'NvCarga\Bundle\Entity\City'
          ));
    }
    public function getName()
    {
        return "city_type";
    }
}
