<?php

namespace App\Form;

use App\Entity\FormSubmissions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [ new NotBlank(['message' => 'Please enter your name'])]
            ])
            ->add('subject', TextType::class, [
                'constraints' => [ new NotBlank(['message' => 'Please enter the subject'])]
            ])
            ->add('message', TextareaType::class, [
                'constraints' => [ new NotBlank(['message' => 'Please enter your message']),
                    new Length([
                        'min' => 4,
                        'minMessage' => 'Your message should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                        'maxMessage' => 'Your message should be at most {{ limit }} characters',
                    ]),
                    ],
                'attr' => ['rows' => 5]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormSubmissions::class,
        ]);
    }
}
