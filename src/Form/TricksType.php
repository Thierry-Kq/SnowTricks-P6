<?php

namespace App\Form;

use App\Entity\Tricks;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TricksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['attr' => ['class' => 'input is-primary', 'placeholder' => 'Alex Smith']])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'input is-primary', 'placeholder' => 'Alex Smith']])
            ->add(
                'images', // pas lié a la bdd
                FileType::class,
                [
                    'attr' => ['class' => 'input file-input'],
                    'label' => false,
                    'multiple' => true,
                    'mapped' => false,
                    'required' => false,
                ]
            )->add(
                'videos', // pas lié a la bdd
                CollectionType::class,
                [
                    'entry_type' => TextType::class,
                    'allow_add' => true,
                    'prototype' => true,
                    'label' => false,
//                    'multiple' => true,
                    'mapped' => false,
                    'required' => false,
                    'allow_delete' => true,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Tricks::class,
            ]
        );
    }
}
