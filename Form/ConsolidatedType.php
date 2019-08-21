<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ConsolidatedType extends AbstractType
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sender', 'entity', array('label' => 'Remitente', 'class' => 'NvCargaBundle:Company',
                'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                        ->where('p.maincompany = :thecompany')
                        ->setParameter('thecompany',$this->user->getMaincompany());},))
            ->add('receiver', 'entity', array('label' => 'Destinatario', 'class' => 'NvCargaBundle:Company',
                'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                        ->where('p.maincompany = :thecompany')
                        ->setParameter('thecompany',$this->user->getMaincompany());},))
            ->add('shippingtype', 'entity', array('label' => 'Tipo de envío' , 'class' => 'NvCargaBundle:Shippingtype' ))
           // ->add('countryfrom', 'entity', array('label' => 'País origen', 'class' => 'NvCargaBundle:Country' ))
           // ->add('countryto', 'entity', array('label' => 'País destino', 'class' => 'NvCargaBundle:Country' ))
           // ->add('office', 'entity', array('label' => 'Oficina ', 'class' => 'NvCargaBundle:Office'))
            ->add('agency', 'entity', array('label' => 'Agencia ', 'class' => 'NvCargaBundle:Agency',
                'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                        ->where('p.maincompany = :thecompany')
                        ->setParameter('thecompany',$this->user->getMaincompany());},))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Consolidated'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'nvcarga_bundle_consolidated';
    }
}
