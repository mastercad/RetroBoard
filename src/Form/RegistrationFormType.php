<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => false,
                    'translation_domain' => 'labels',
                    'attr' => [
                        'placeholder' => 'Name',
                        'class' => 'fadeIn second'
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a name',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Your name should be at least {{ limit }} characters',
                //                            'minMessage' => 'fvxWW3V',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                ]
            )
            ->add(
                'email',
                TextType::class,
                [
                    'label' => false,
                    'translation_domain' => 'labels',
                    'attr' => [
                        'placeholder' => 'E-Mail',
                        'class' => 'fadeIn third'
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a valid e-mail',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                        new Email(
                        )
                    ],
                ]
            )
            ->add(
                'password',
                PasswordType::class,
                [
                    // instead of being set onto the object directly,
                    // this is read and encoded in the controller
                    'label' => false,
                    'mapped' => false,
                    'translation_domain' => 'labels',
                    'attr' => [
                        'placeholder' => 'password',
                        'class' => 'fadeIn third'
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                ]
            )
            ->add(
                'agreeTerms',
                CheckboxType::class,
                [
                    'label' => 'agree_terms',
                    'mapped' => false,
                    'translation_domain' => 'labels',
                    'attr' => [
                        'class' => 'fadeIn third',
                        'style' => 'margin-left: 10px'
                    ],
                    'label_attr' => [
                        'class' => 'fadeIn third'
                    ],
                    'constraints' => [
                        new IsTrue(
                            [
                                'message' => 'You should agree to our terms.',
                            ]
                        ),
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
