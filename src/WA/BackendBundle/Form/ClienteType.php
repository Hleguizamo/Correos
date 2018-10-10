<?php

namespace WA\BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClienteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('zona')
            ->add('codigo')
            ->add('bloqueoD')
            ->add('drogueria')
            ->add('nit')
            ->add('bloqueoR')
            ->add('asociado')
            ->add('nitDv')
            ->add('direcion')
            ->add('codMun')
            ->add('ciudad')
            ->add('ruta')
            ->add('unGeogra')
            ->add('depto')
            ->add('telefono')
            ->add('centro')
            ->add('pS')
            ->add('pCarga')
            ->add('diskette')
            ->add('clienteTiempo', 'datetime')
            ->add('email')
            ->add('emailAsociado')
            ->add('tipoCliente')
            ->add('cupoAsociado')
            ->add('estado')
            ->add('centroCosto')
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'WA\BackendBundle\Entity\Cliente'
        ));
    }
}
