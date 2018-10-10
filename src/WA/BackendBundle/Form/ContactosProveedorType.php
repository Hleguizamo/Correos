<?php

namespace WA\BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class ContactosProveedorType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombreContacto')
            ->add('ciudad')
            ->add('email')
            ->add('telefono')
            ->add('movil')
            ->add('idProveedor', EntityType::class, array(
                'class' => 'WABackendBundle:Proveedores',
                'query_builder' => function (EntityRepository $e) use($options) {
                    return $e->createQueryBuilder('p')
                        ->where('p.id = :id')
                        ->setParameter('id', $options['id']);
                },
                'choice_label' => 'nombre',
            ))
            ->add('idCargo', EntityType::class, array(
                'class' => 'WABackendBundle:Cargos',
                'choice_label' => 'nombre',
            ))
            ->add('idArea', EntityType::class, array(
                'class' => 'WABackendBundle:Areas',
                'choice_label' => 'nombre',
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'WA\BackendBundle\Entity\ContactosProveedor',
            'id' => null
        ));
    }
}
