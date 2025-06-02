<?php

namespace App\DataFixtures;

use App\Entity\Trajet;
use App\Entity\User;
use App\Entity\Vehicule;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TrajetFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        
        // créer un chauffeur fictif 

        $chauffeur = new User();
        $chauffeur->setEmail('test@example.com')
                  ->setPassword('hashedpassword') 
                  ->setPseudo('EcoChauffeur')
                  ->setCredits(20)
                  ->setRole('ROLE_USER')
                  ->setCreatedAt(new \DateTimeImmutable())
                  ->setApiToken(bin2hex(random_bytes(20)));

        $vehicule = new Vehicule();
        $vehicule->setMarque('Renault')
                 ->setModele('ZOE')
                 ->setImmatriculation('AB-123-CD')
                 ->setTypeEnergie('électrique')
                 ->setPreferChien(true)
                 ->setPreferFumeur(false)
                 ->setCreatedAt(new \DateTimeImmutable())
                 ->setUtilisateur($chauffeur);

        $manager->persist($chauffeur);
        $manager->persist($vehicule);

        for ($i = 0; $i < 5; $i++) {
            $trajet = new Trajet();
            $trajet->setVilleDepart('Paris')
                   ->setVilleArrivee('Lyon')
                   ->setDateDepart(new \DateTimeImmutable('+'.($i + 1).' days'))
                   ->setPlacesTotal(4)
                   ->setPlacesRestantes(2)
                   ->setPrix(25.50 + $i)
                   ->setIsEcoCertifie(true)
                   ->setCreatedAt(new \DateTimeImmutable())
                   ->setDuree(120 + $i * 10)
                   ->setChauffeur($chauffeur)
                   ->setVehicule($vehicule);

            $manager->persist($trajet);
        }

        $manager->flush();
    }
}
