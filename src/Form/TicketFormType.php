<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketFormType extends AbstractType 
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content',
            TextareaType::class, 
            [
                'label_format' => '%name%',
                'translation_domain' => 'labels'
            ]
        );
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'csrf_protection' => false,
            ]
        );
    }
}