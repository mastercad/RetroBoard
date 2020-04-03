<?php

namespace App\Form;

use App\Entity\BoardMember;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class BoardMemberType extends AbstractType
{
    private $tokenStorage = null;
    private $authenticationManager = null;
    private $security = null;

//    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager)
//    public function __construct(UserInterface $user)
//    public function __construct(Security $security)
//    {
//        $this->security = $security;
//        $this->authenticationManager = $authenticationManager;
//        $this->tokenStorage = $tokenStorage;
//        $this->user = $user;
//    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('user')
            ->add('board')
            ->add('roles')
            ->add('created', HiddenType::class, [
//                    'format' => \IntlDateFormatter::SHORT,
//                    'format' => 'Y-m-d H:i:s',
//                    'input' => 'datetime',
//                    'widget' => 'single_text',
                    'data' => new \DateTime("now")
                ]
            )
//            ->add('creator', HiddenType::class, [
//                'class' => User::class,
//                'attr' => ['style' => 'display: none']
//                'data' => $this->tokenStorage->getToken()->getUser()
//                'data' => $this->authenticationManager->authenticate($this->tokenStorage->getToken())->getUser()
//                'data' => $this->security->getUser()
//                'data' => $options['creator']
//            ])
        ;

//        var_dump($this->authenticationManager->authenticate($this->tokenStorage->getToken())->getUser()->getName());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BoardMember::class,
            'creator' => null
        ])->setRequired('creator');
    }

    public function getAlias()
    {
        return "BoardMemberTypeFORMClassDingsBums";
    }
}
