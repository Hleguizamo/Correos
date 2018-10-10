<?php

namespace WA\BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdministradorType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder
      ->add('nombre')
      ->add('usuario')
      ->add('clave')
      ->add('email')
      ->add('claveEmail')
      ->add('tipoUsuario', ChoiceType::class,
          array(
              'choices' => array(
              'Administrador' => '1',
              'Usuario' => '0'
              ),
              'required'    => true,
              'placeholder' => '.:usuario:.',
              'empty_data'  => null
            )
          )     
      ->add('activo', ChoiceType::class,
          array(
            'choices' => array(
              'Activo' => '1',
              'Inactivo' => '0'
            ),
            'required'    => true,
            'placeholder' => '.:estado:.',
            'empty_data'  => null
          )) 
      ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
      $resolver->setDefaults(array(
        'data_class' => 'WA\BackendBundle\Entity\Administrador'
        ));
    }
  }
