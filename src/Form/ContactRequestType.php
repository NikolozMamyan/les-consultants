<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\ContactRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContactRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('profile', HiddenType::class)
            ->add('name', TextType::class, ['empty_data' => ''])
            ->add('email', EmailType::class, ['empty_data' => ''])
            ->add('phone', TelType::class, ['required' => false])
            ->add('subject', ChoiceType::class, [
                'empty_data' => '',
                'placeholder' => 'Choisir un sujet',
                'choices' => [
                    'Compliance & réglementation' => 'compliance',
                    'Finance & fonds' => 'finance',
                    'Risques & gouvernance' => 'risques',
                    'Juridique & réglementaire' => 'juridique',
                    'Transformation & IT' => 'digital',
                    'Formation' => 'formation',
                    'Autre besoin' => 'autre',
                ],
            ])
            ->add('message', TextareaType::class, ['empty_data' => ''])
            ->add('consent', CheckboxType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ContactRequest::class]);
    }
}
