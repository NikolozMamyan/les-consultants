<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\BlogPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class BlogPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $imageConstraint = new Assert\Image(
            maxSize: '8M',
            mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
            mimeTypesMessage: 'Utilisez une image JPG, PNG ou WebP.',
        );

        $builder
            ->add('title', TextType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            ])
            ->add('slug', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'constraints' => [new Assert\Length(max: 255), new Assert\Regex('/^[a-z0-9-]*$/')],
            ])
            ->add('excerpt', TextareaType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 1200)],
            ])
            ->add('content', TextareaType::class, [
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('category', TextType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 80)],
            ])
            ->add('author', TextType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 120)],
            ])
            ->add('featuredImageFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [$imageConstraint],
            ])
            ->add('featuredImageRemove', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('featuredImageAlt', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'constraints' => [new Assert\Length(max: 255)],
            ])
            ->add('contentImageFiles', FileType::class, [
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'constraints' => [new Assert\All([$imageConstraint])],
            ])
            ->add('removeContentImages', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => $options['content_image_choices'],
            ])
            ->add('metaTitle', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'constraints' => [new Assert\Length(max: 255)],
            ])
            ->add('metaDescription', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
                'constraints' => [new Assert\Length(max: 255)],
            ])
            ->add('publishedAt', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('published', CheckboxType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlogPost::class,
            'content_image_choices' => [],
        ]);
        $resolver->setAllowedTypes('content_image_choices', 'array');
    }
}
