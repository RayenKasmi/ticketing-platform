<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Events;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
            ->add('name', TextType::class,[
                'constraints' => [new NotBlank(['message' => 'Field must not be empty'])]
            ])
            ->add('venue', TextType::class,[
                'constraints' => [new NotBlank(['message' => 'Field must not be empty'])]
            ])
            ->add('shortDescription', TextType::class,[
                'constraints' => [new NotBlank(['message' => 'Field must not be empty'])]
            ])
            ->add('longDescription', TextType::class,[
                'constraints' => [new NotBlank(['message' => 'Field must not be empty'])]
            ])
            ->add('organizer', TextType::class,[
                'constraints' => [new NotBlank(['message' => 'Field must not be empty'])]
            ])
            ->add('totalTickets')
            ->add('availableTickets')
            ->add('startSellTime', null, [
                'widget' => 'single_text',
            ])
            ->add('eventDate', null, [
                'widget' => 'single_text',
            ])
            ->add('ticketPrice')
            ->add('category', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'name',
            ])
            ->add('imagePath', FileType::class, [
                'label' => "Event's Image (image file)",

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using attributes
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ])
                ],
            ])
            ->add('submit' , SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Events::class,
        ]);
    }
}
