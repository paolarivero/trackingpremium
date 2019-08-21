<?php

namespace NvCarga\Bundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class BillType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//          ->add('number') // el número se debe generar
//           ->add('creationdate') // se colaca la fecha de creación
//	     ->add('customer', null, array('label' => 'Cliente ')) // cliente se debe especificar si es el destinatario o el remitente de las guías
//            ->add('status') // status de la factura
//	    ->add('guides') // lista de guias que van en la factura. Una guia solo debe estar en una factura
	    ->add('paidtype', 'entity', array('label' => 'Tipo de pago ',  
			'class'=> 'NvCargaBundle:Paidtype', 
			'query_builder' => function (EntityRepository $er) {
        			return $er->createQueryBuilder('p')
					->where('p.active = true')
					->andwhere('p.deleted = false')
            				->orderBy('p.name', 'ASC');
    				}, 
			'required' => true, 
			'constraints' => array(new NotBlank(["message" => "Seleccione el tipo de pago"]))))	    
	    ->add('billto', 'choice', array('choices'  => array('Remitente' => true, 'Destinatario' => false),
    			'choices_as_values' => true, 'mapped' => false, 'required' => true, 'label'=>'Facturar a:', 
						'constraints' => array(new NotBlank(["message" => "Seleccione a quien se factura"]))))
        ;
        ->add('status', 'choice', array('label' => 'Status ', 
                    'choices' => array('Pendiente' => 'Pendiente', 'Cobrada' => 'COBRADA'), 'choices_as_values' => true))
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Bill'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'nvcarga_bundle_bill';
    }
}
