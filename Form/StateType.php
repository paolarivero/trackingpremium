<?php

namespace NvCarga\Bundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class StateType extends AbstractType
{

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
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'Nombre '))
 //           ->add('creationdate')
            ->add('country', 'entity', array('label'=> 'País ', 'class'=> 'NvCargaBundle:Country',
					'choices' => $this->maincompany->getCountries()->toArray()));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\State'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'state_type';
    }
}
