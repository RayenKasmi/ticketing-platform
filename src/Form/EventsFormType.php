<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Events;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [new NotBlank(['message' => 'Please enter the name'])],
                'required' => false,
            ])
            ->add('venue', TextType::class, [
                'constraints' => [new NotBlank(['message' => 'Please enter the venue'])],
                'required' => false,
            ])
            ->add('shortDescription', TextType::class, [
                'constraints' => [new NotBlank(['message' => 'Please enter the short description'])],
                'required' => false,
            ])
            ->add('longDescription', TextType::class, [
                'constraints' => [new NotBlank(['message' => 'Please enter the long description'])],
                'required' => false,
            ])
            ->add('organizer', TextType::class, [
                'constraints' => [new NotBlank(['message' => 'Please enter the organizer'])],
                'required' => false,
            ])
            ->add('totalTickets', NumberType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please enter the total tickets']),
                ],
                'required' => false,
            ])
            ->add('availableTickets', NumberType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please enter the available tickets']),
                ],
                'required' => false,
            ])
            ->add('startSellTime', DateType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please enter the start sell time']),
                ],
            ])
            ->add('eventDate', DateType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please enter the event date']),
                ],
            ])
            ->add('ticketPrice', MoneyType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please enter the ticket price']),
                ],
                'required' => false,
                'currency' => 'USD',
            ])
            ->add('category', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank(['message' => 'Please select a category']),
                ],
            ])
            ->add('imagePath', FileType::class, [
                'label' => "Event's Image (image file)",

                // unmapped means that this field is not associated with any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the image file every time you edit the event details
                'required' => false,

                // unmapped fields can't define their validation using attributes in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => ['image/png', 'image/jpeg', 'image/jpg'],
                        'mimeTypesMessage' => 'Please upload a valid image (PNG, JPEG, JPG)',
                    ])
                ],
            ])



        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Events::class,
        ]);
    }
}
