<?php

namespace App\DataFixtures;

use App\Entity\Participation;
use App\Entity\Trajet;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;

class ParticipationFixtures extends Fixture
{
    public function __construct(private ManagerRegistry $doctrine) {}

    public function load(ObjectManager $manager): void
    {
        $em = $this->doctrine->getManager();

        // On récupère un utilisateur existant (passager)
        $passager = new User();
        $passager->setEmail('passager@fixture.com')
                 ->setPseudo('PassagerFixture')
                 ->setPassword('passager')
                 ->setCredits(100)
                 ->setRoles(['ROLE_USER'])
                 ->setCreatedAt(new \DateTimeImmutable())
                 ->setApiToken(bin2hex(random_bytes(20)));

        $manager->persist($passager);

        // récupèrer quelques trajets existants pour participer
        $trajets = $em->getRepository(Trajet::class)->findBy([], null, 3); // max 3 trajets

        foreach ($trajets as $trajet) {
            $participation = new Participation();
            $participation->setUser($passager);
            $participation->setTrajet($trajet);
            $participation->setCreatedAt(new \DateTimeImmutable());

            // Décrémente les crédits et les places restantes
            $passager->setCredits($passager->getCredits() - $trajet->getPrix());
            $trajet->setPlacesRestantes(max(0, $trajet->getPlacesRestantes() - 1));

            $manager->persist($participation);
            $manager->persist($trajet);
        }

        $manager->flush();
    }
}
