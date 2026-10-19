<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\EventRegistration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EventRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'empty_data' => '',
                'attr' => ['autocomplete' => 'given-name', 'maxlength' => 100, 'placeholder' => 'Votre prénom'],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'empty_data' => '',
                'attr' => ['autocomplete' => 'family-name', 'maxlength' => 100, 'placeholder' => 'Votre nom'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'empty_data' => '',
                'attr' => ['autocomplete' => 'email', 'maxlength' => 180, 'placeholder' => 'vous@entreprise.com'],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'empty_data' => '',
                'attr' => ['autocomplete' => 'tel', 'maxlength' => 35, 'placeholder' => '+352 …'],
            ])
            ->add('consent', CheckboxType::class, [
                'label' => 'J’accepte que mes coordonnées soient utilisées pour gérer mon inscription à cet événement.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventRegistration::class,
            'csrf_message' => 'Le formulaire a expiré. Veuillez réessayer.',
        ]);
    }
}
