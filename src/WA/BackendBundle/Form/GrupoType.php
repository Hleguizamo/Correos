<?php

namespace WA\BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class GrupoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre',TextType::class, array(
                    'label' => 'Nombre:* ',
                    'attr' => array(
                        'class' => 'form-control form-control-sm'
                    )
            ))
            ->add('clasificacion',ChoiceType::class, array(
                'choices' => array(
                    'No Aplica'=>'No Aplica',
                    'Droguerias'=>'Droguerías', 
                    'Asociados'=>'Asociados'),
                'label' => 'Clasificación:',
                'attr' => array('class'=>'selectpicker')
            ))
            ->add('descripcion',TextType::class, array(
                    'label' => 'Descripcion:* ',
                    'attr' => array(
                        'class' => 'form-control form-control-sm'
                    )
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'WA\BackendBundle\Entity\Grupo'
        ));
    }
}
