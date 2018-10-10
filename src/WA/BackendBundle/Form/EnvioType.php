<?php

namespace WA\BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityRepository;

class EnvioType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $administrador = $options['id_administrador'];
      $builder
      ->add('asunto')
      ->add('contenido')
      ->add('estado')
      ->add('adjuntos')
      ->add('grupo',EntityType::class,array(
        'class' => 'WABackendBundle:Grupo',
        'choice_label' => 'nombre',
        'query_builder' => function (EntityRepository $er) use ( $administrador ) {
          return $er->createQueryBuilder('g')
          ->where('g.administrador IN ('.$administrador.')')
          ->orderBy('g.nombre', 'ASC');
        }
        ))
      ->add('administrador',EntityType::class,array(
        'class' => 'WABackendBundle:Administrador',
        'choice_label' => 'nombre',
        'query_builder' => function (EntityRepository $er) {
          return $er->createQueryBuilder('a')
          ->orderBy('a.nombre', 'ASC');
        }
        ))
      ->add('columnaCombinacion', ChoiceType::class, array(
          'choices'  => array(
              'No aplica' => null,
              'Codigo' => 'Codigo',
              'Nit' => 'Nit'
            )
          ))
        // ->add('fechaEnvio', 'datetime')
        //->add('cantidadEnviados')
        // ->add('fechaCreado', 'datetime')
        //->add('combinacion')
        
        //->add('programado')
        // ->add('copiaAdministrador')
      
      ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
      $resolver->setRequired('id_administrador');
      $resolver->setDefaults(array(
        'data_class' => 'WA\BackendBundle\Entity\Envio'
        ));
    }
  }
