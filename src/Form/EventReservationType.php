<?php

namespace App\Form;

use App\Entity\EventReservation;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManagerInterface;


class EventReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('POST')
            ->setAttribute('id', 'buyTicketsForm')
            ->add('quantity', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'min' => 1,
                    'class' => 'form-control form-control-sm text-center border border-1',
                    'style' => 'width: 100px;',

                ],
                'data' => 1,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'integer']),
                    new Assert\Positive(['message' => 'The quantity must be a positive integer.']),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Buy Tickets',
                'attr' => ['class' => 'btn border border-secondary rounded-pill px-4 py-2 mb-4 text-primary']
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventReservation::class,
        ]);
    }
}
