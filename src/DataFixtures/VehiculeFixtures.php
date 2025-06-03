<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Vehicule;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;

class VehiculeFixtures extends Fixture
{
    public function __construct(private ManagerRegistry $doctrine) {}

    public function load(ObjectManager $manager): void
    {
        $em = $this->doctrine->getManager();

        // récupèrer tous les utilisateurs existants
        $users = $em->getRepository(User::class)->findAll();

        if (empty($users)) {
            throw new \RuntimeException('Aucun utilisateur trouvé. Exécute d’abord UserFixtures.');
        }

        $marques = ['Renault', 'Peugeot', 'Tesla', 'Citroën'];
        $modeles = ['ZOE', '208', 'Model 3', 'C3'];
        $energies = ['électrique', 'essence', 'hybride', 'diesel'];

        foreach ($users as $index => $user) {
            $vehicule = new Vehicule();
            $vehicule->setMarque($marques[$index % count($marques)])
                     ->setModele($modeles[$index % count($modeles)])
                     ->setImmatriculation('XX-' . rand(100, 999) . '-ZZ')
                     ->setTypeEnergie($energies[$index % count($energies)])
                     ->setPreferFumeur((bool)random_int(0, 1))
                     ->setPreferChien((bool)random_int(0, 1))
                     ->setCreatedAt(new \DateTimeImmutable())
                     ->setUtilisateur($user);

            $manager->persist($vehicule);
        }

        $manager->flush();
    }
}
