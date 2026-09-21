<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use App\Service\AdminUserManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $passwordConstraints = [new Length(min: 12, minMessage: 'Utilisez au moins {{ limit }} caractères.')];
        if ($options['password_required']) {
            $passwordConstraints[] = new NotBlank(message: 'Choisissez un mot de passe.');
        }

        $builder
            ->add('displayName', TextType::class, [
                'label' => 'Nom affiché',
                'attr' => ['autocomplete' => 'name'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => ['autocomplete' => 'email'],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Niveau d’accès',
                'mapped' => false,
                'data' => $options['role'],
                'disabled' => $options['lock_access'],
                'choices' => [
                    'Administrateur' => AdminUserManager::ROLE_ADMIN,
                    'Super administrateur' => AdminUserManager::ROLE_SUPER_ADMIN,
                ],
                'help' => 'Le super administrateur peut aussi gérer les comptes.',
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Compte actif',
                'required' => false,
                'disabled' => $options['lock_access'],
                'help' => $options['lock_access'] ? 'Vous ne pouvez pas désactiver votre propre compte.' : 'Un compte désactivé ne peut plus se connecter.',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => $options['password_required'],
                'invalid_message' => 'Les deux mots de passe ne correspondent pas.',
                'constraints' => $passwordConstraints,
                'first_options' => [
                    'label' => $options['password_required'] ? 'Mot de passe' : 'Nouveau mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                    'help' => $options['password_required'] ? '12 caractères minimum.' : 'Laissez vide pour conserver le mot de passe actuel.',
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'password_required' => false,
            'role' => AdminUserManager::ROLE_ADMIN,
            'lock_access' => false,
        ]);
        $resolver->setAllowedTypes('password_required', 'bool');
        $resolver->setAllowedTypes('role', 'string');
        $resolver->setAllowedTypes('lock_access', 'bool');
    }
}
