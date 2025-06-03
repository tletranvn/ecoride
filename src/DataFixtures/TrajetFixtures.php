<?php

namespace App\DataFixtures;

use App\Entity\Trajet;
use App\Entity\User;
use App\Entity\Vehicule;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TrajetFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // Créer un utilisateur fictif (chauffeur)
        $chauffeur = new User();
        $chauffeur->setEmail('chauffeur@fixture.com');
        $chauffeur->setPseudo('ChauffeurFixture');
        $chauffeur->setPassword($this->hasher->hashPassword($chauffeur, 'motdepasse'));
        $chauffeur->setCredits(100);
        $chauffeur->setRoles(['ROLE_USER']);

        // Créer un véhicule associé au chauffeur
        $vehicule = new Vehicule();
        $vehicule->setMarque('Renault');
        $vehicule->setModele('Zoé');
        $vehicule->setImmatriculation('AA-000-AA');
        $vehicule->setTypeEnergie('électrique');
        $vehicule->setPreferFumeur(false);
        $vehicule->setPreferChien(true);
        $vehicule->setCreatedAt(new \DateTimeImmutable());
        $vehicule->setUtilisateur($chauffeur);

        $manager->persist($chauffeur);
        $manager->persist($vehicule);

        // Générer 5 trajets de démonstration
        for ($i = 1; $i <= 5; $i++) {
            $trajet = new Trajet();
            $trajet->setVilleDepart('Paris');
            $trajet->setVilleArrivee('Lyon');
            $trajet->setDateDepart(new \DateTimeImmutable("+$i days"));
            $trajet->setPlacesTotal(4);
            $trajet->setPlacesRestantes(2);
            $trajet->setPrix(20 + $i); // entre 21 et 25
            $trajet->setIsEcoCertifie(true);
            $trajet->setDuree(100 + $i * 10);
            $trajet->setCreatedAt(new \DateTimeImmutable());
            $trajet->setChauffeur($chauffeur);
            $trajet->setVehicule($vehicule);

            $manager->persist($trajet);
        }

        $manager->flush();
    }
}
