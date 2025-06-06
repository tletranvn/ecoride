<?php

namespace App\Form;

use App\Entity\Trajet;
use App\Entity\Vehicule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
//use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\SecurityBundle\Security;

class TrajetType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();

        $builder
            // Champ avec datalist pour la ville de départ
            ->add('villeDepart', TextType::class, [
                'label' => 'Ville de départ',
                'attr' => ['list' => 'villes'] // <- associe au <datalist id="villes"> dans le template
            ])

            // Champ avec datalist pour la ville d’arrivée
            ->add('villeArrivee', TextType::class, [
                'label' => 'Ville d’arrivée',
                'attr' => ['list' => 'villes'] // <- même principe
            ])

            // Date et heure dans un seul champ
            ->add('dateDepart', DateTimeType::class, [
                'label' => 'Date et heure de départ',
                'widget' => 'single_text'
            ])

            // Nombre de places proposées
            ->add('placesTotal', IntegerType::class, [
                'label' => 'Nombre total de places'
            ])

            // Prix avec format monétaire
            ->add('prix', MoneyType::class, [
                'label' => 'Prix du trajet (€)',
                'currency' => 'EUR'
            ])

            // Sélection du véhicule parmi ceux du user connecté
            ->add('vehicule', EntityType::class, [
                'class' => Vehicule::class,
                'choices' => $user ? $user->getVehicules() : [],
                'choice_label' => function (Vehicule $v) {
                    return $v->getMarque() . ' ' . $v->getModele() . ' (' . $v->getImmatriculation() . ')';
                },
                'label' => 'Véhicule utilisé',
                'placeholder' => 'Sélectionner un véhicule',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trajet::class,
        ]);
    }
}
