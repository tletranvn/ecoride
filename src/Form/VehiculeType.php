<?php

namespace App\Form;

use App\Entity\Vehicule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehiculeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque', TextType::class)
            ->add('modele', TextType::class)
            ->add('immatriculation', TextType::class)
            ->add('typeEnergie', ChoiceType::class, [
                'choices' => [
                    'Essence' => 'essence',
                    'Diesel' => 'diesel',
                    'Électrique' => 'électrique',
                    'Hybride' => 'hybride',
                ],
                'label' => 'Type d\'énergie',
            ])
            ->add('places', IntegerType::class, [
                'label' => 'Nombre de places disponibles'
            ])
            ->add('preferFumeur', CheckboxType::class, [
                'label' => 'Fumeur accepté',
                'required' => false,
            ])
            ->add('preferChien', CheckboxType::class, [
                'label' => 'Animaux acceptés',
                'required' => false,
            ])
            ->add('preferencesLibres', TextareaType::class, [
                'label' => 'Autres préférences (optionnel)',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}
