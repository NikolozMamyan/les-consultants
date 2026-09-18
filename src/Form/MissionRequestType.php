<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\MissionRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MissionRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $expertises = [
            'Compliance & réglementation',
            'Contrôle interne & gouvernance',
            'Services juridiques & réglementaires',
            'Fund Administration',
            'Transformation digitale (IT)',
            'Formation',
            'Autre',
        ];

        $builder
            ->add('missionTitle', TextType::class, ['empty_data' => ''])
            ->add('expertise', ChoiceType::class, [
                'empty_data' => '',
                'placeholder' => 'Choisir une expertise',
                'choices' => array_combine($expertises, $expertises),
            ])
            ->add('startDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('duration', ChoiceType::class, [
                'empty_data' => '',
                'placeholder' => 'Choisir une durée',
                'choices' => array_combine(
                    ['Moins de 3 mois', '3 à 6 mois', 'Plus de 6 mois', 'À définir'],
                    ['Moins de 3 mois', '3 à 6 mois', 'Plus de 6 mois', 'À définir'],
                ),
            ])
            ->add('company', TextType::class, ['empty_data' => ''])
            ->add('contactName', TextType::class, ['empty_data' => ''])
            ->add('email', EmailType::class, ['empty_data' => ''])
            ->add('phone', TelType::class, ['required' => false])
            ->add('description', TextareaType::class, ['empty_data' => ''])
            ->add('consent', CheckboxType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => MissionRequest::class]);
    }
}
