<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\ConsultantApplication;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConsultantApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $financeRoles = array_combine(ConsultantApplication::FINANCE_ROLES, ConsultantApplication::FINANCE_ROLES);
        $itRoles = array_combine(ConsultantApplication::IT_ROLES, ConsultantApplication::IT_ROLES);

        $builder
            ->add('firstName', TextType::class, ['empty_data' => ''])
            ->add('lastName', TextType::class, ['empty_data' => ''])
            ->add('email', EmailType::class, ['empty_data' => ''])
            ->add('phone', TelType::class, ['empty_data' => ''])
            ->add('primaryDomain', ChoiceType::class, [
                'empty_data' => '',
                'placeholder' => 'Choisir un domaine',
                'choices' => ['Finance' => 'Finance', 'IT' => 'IT'],
            ])
            ->add('targetRoles', ChoiceType::class, [
                'choices' => [
                    'Finance' => $financeRoles,
                    'IT' => $itRoles,
                ],
                'multiple' => true,
                'attr' => ['size' => 7],
            ])
            ->add('dailyRateMin', IntegerType::class, [
                'attr' => ['min' => 0, 'max' => 5000, 'step' => 25],
            ])
            ->add('dailyRateMax', IntegerType::class, [
                'required' => false,
                'attr' => ['min' => 0, 'max' => 5000, 'step' => 25],
            ])
            ->add('countries', ChoiceType::class, [
                'choices' => ['Belgique' => 'Belgique', 'Luxembourg' => 'Luxembourg'],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('availability', ChoiceType::class, [
                'empty_data' => '',
                'placeholder' => 'Choisir votre disponibilité',
                'choices' => ['Disponible' => 'Disponible', 'Non disponible' => 'Non disponible'],
            ])
            ->add('cv', FileType::class, [
                'attr' => ['accept' => 'application/pdf,.pdf'],
            ])
            ->add('consent', CheckboxType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ConsultantApplication::class]);
    }
}
