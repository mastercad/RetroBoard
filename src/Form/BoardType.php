<?php

namespace App\Form;

use App\Entity\Board;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BoardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('name')
            ->add('columns', CollectionType::class, [
                'entry_type' => ColumnType::class,
                'allow_add' => true,
                'allow_delete' => true,
//                'prototype' => true,
                'by_reference' => false
            ])
//            ->add('boardMembers', CollectionType::class, [
//                'entry_type' => BoardMemberType::class,
//                'allow_add' => true,
//                'allow_delete' => true,
//                'prototype' => true,
//                'by_reference' => false
//            ])
//            ->add('created')
//            ->add('modified')
//            ->add('creator')
//            ->add('modifier')
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Board::class,
            'creator' => null
        ]);
    }

    public function getAlias()
    {
        return 'BoardTypeFormClassDingsBums';
    }
}
