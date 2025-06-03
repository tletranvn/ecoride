<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            ['email' => 'alice@example.com', 'pseudo' => 'Alice', 'password' => 'alicepass'],
            ['email' => 'bob@example.com', 'pseudo' => 'Bob', 'password' => 'bobpass'],
            ['email' => 'carol@example.com', 'pseudo' => 'Carol', 'password' => 'carolpass'],
        ];

        foreach ($usersData as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setPseudo($data['pseudo']);
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $data['password'])
            );

            // Ces valeurs sont déjà définies dans le constructeur, donc cette ligne est optionnelle
            // $user->setCredits(20);
            // $user->setRoles(['ROLE_USER']);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
