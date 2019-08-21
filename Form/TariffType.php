<?php
namespace NvCarga\Bundle\Form;

use Doctrine\ORM\EntityManager;
use NvCarga\Bundle\Entity\Country;
use NvCarga\Bundle\Entity\Region;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TariffType extends AbstractType {
    
    protected $user;
    protected $maincompany;
    protected $em;
    function __construct(EntityManager $em, $user)
    {
        $this->em = $em;
        $this->user = $user;
        $this->maincompany = $user->getMaincompany();
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Add listeners
        $builder
            //->add('agency', null, array('label' => 'Agencia',))
            //->add('service', null, array('label' => 'Tipo de Servicio',))
            ->add('name', null, array('label' => 'Nombre ',))
            ->add('measure', 'entity', array('label' => 'Medida ', 'choice_label' => 'label', 
                        'class' => 'NvCargaBundle:Measure', 'multiple'=>false, 'expanded'=>false)) 
            ->add('insurance', null, array('label' => 'Seguro',))
            ->add('tax', null, array('label' => 'Impuesto',))
            ->add('dimentional', null, array('label' => 'Dimensional',))
            ->add('cost', 'number', array('label' => 'Costo', ))
            ->add('begin', 'number', array('label' => 'Inicio '
                // 'attr'=>array('data-help'=>'Límite mínimo de la dimensión (en CF/libras)')
                ))
            ->add('until', 'number', array('label' => 'Hasta ', 
                //'attr'=>array('data-help'=>'Límite máximo de la dimensión (en CF/libras)')
                ))
                //->add('lastupdate')
            ->add('minimun', 'number', array('label' => 'Límite Mínimo ',))
            ->add('value_measure', null, array('label' => 'Valor($)', 
                // 'attr'=>array('data-help'=>'Lo que se cobra por la UNIDAD de medida')
                ))
            ->add('value_min', 'number', array('label' => 'Valor mín($) ', ))
            ->add('minimun_limit', 'choice', array('label' => 'Cobrar mínimo ', 
                    'choices' => array('Ninguno' => 'Ninguno', 'Individual' => 'Individual', 'Total'=>'Total'), 'choices_as_values' => true))
            ->add('profit_ag', null, array('label' => 'Ganancia($)'))
            ->add('volume_price', null, array('label' => 'Precio Vol($)'))
            ->add('profit_agv', null, array('label' => 'Ganancia Vol($)'))
            ->add('additional', null, array('label' => 'Adicional'))
            ->add('label_additional', null, array('label' => 'Etiqueta adicional'))
//             ->add('region', 'entity', array('label'=> 'Región/País ', 'class' => 'NvCargaBundle:Region',
//                         'query_builder' => function (EntityRepository $er) {
//                         return $er->createQueryBuilder('p')
//                         ->where('p.maincompany = :thecompany')
//                         ->andWhere('p.country IN (:countries)')
//                         ->setParameters(['thecompany'=>$this->user->getMaincompany(), 'countries'=>$this->user->getMaincompany()->getCountries()->toArray()]);},))
            ->add('insurance_per', 'number', array('label' => 'seguro(%) ','scale' => 2))
            ->add('tax_per', 'number', array('label' => 'impuesto(%) ', 'scale' => 2))
            ->add('weightpay', 'choice', array('label' => 'Cobrar Peso ',
                        'choices' => array('Mayor entre real y volumétrico' => 'Mayor', 
                        'Peso Real' => 'Peso', 'Peso Volumétrico' => 'Peso Volumen'),'choices_as_values' => true,))
            ;
           //  ->add('country', null, array('label' => 'País',));
           $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
           $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }
    protected function addElements(FormInterface $form, Country $country = null, Region $region = null) {
        $form->add('country', 'entity', array('label'=> 'País ',
            'data' => $country,
            'empty_value' => '-- Escoja el País --',
            'class' => 'NvCargaBundle:Country',
            'mapped' => false,
            'choices'=>$this->maincompany->getCountries()->toArray(),
            ));
        // States are empty, unless we actually supplied a country
        $regions = array();
        
        if ($country) {
            $regions = $this->em->getRepository('NvCargaBundle:Region')->findBy(['maincompany'=>$this->maincompany, 'country'=>$country]); 
        }
        if (($region) && ($country)) {
            $form->add('region', 'entity', array('label'=> 'Región ',
                'empty_value' => '-- Seleccione primero el País --',
                'class' => 'NvCargaBundle:Region',
                'data' => $region,
                'choices' => $regions));
        } else {
            $form->add('region', 'entity', array('label'=> 'Región ',
                'empty_value' => '-- Seleccione primero el País --',
                'class' => 'NvCargaBundle:Region',
                'choices' => array(),));
        }
    }
    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();
        // Note that the data is not yet hydrated into the entity.
        $country = $this->em->getRepository('NvCargaBundle:Country')->find($data['country']);
        $region = $this->em->getRepository('NvCargaBundle:Region')->find($data['region']);
        $this->addElements($form, $country, $region);
    }
    function onPreSetData(FormEvent $event) {
        $tariff = $event->getData();
        $form = $event->getForm();
        // We might have an empty account (when we insert a new account, for instance)
        $country = $tariff->getRegion() ? $tariff->getRegion()->getCountry() : null;
        $this->addElements($form, $country, $tariff->getRegion());
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
          $resolver->setDefaults(array(
              'data_class' => 'NvCarga\Bundle\Entity\Tariff'
          ));
    }
    public function getName()
    {
        return "tariff_type";
    }
}
