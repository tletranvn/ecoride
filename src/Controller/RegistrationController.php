<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Set the default role/credits for the user : pas besoin car on a déjà défini dans l'entité User avec constructeur
            //$user->setRoles(['ROLE_USER']); // Assurez-vous que l'utilisateur a au moins le rôle USER
            //$user->setCredits(20); // Initialiser les crédits à 20
            //$user->setCreatedAt(new \DateTimeImmutable()); // Initialiser la date de création

            //enregistrer l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Connecter l'utilisateur après l'enregistrement jusqu'à US7
            //return $security->login($user, 'form_login', 'main');

            //ajouter la condition de redirection selon userType pour US8
            $response = $security->login($user, 'form_login', 'main');

            if (in_array($user->getUserType(), ['chauffeur', 'passager_chauffeur'])) {
                return $this->redirectToRoute('app_vehicule_new'); // Rediriger vers la page de création de véhicule si le userType est chauffeur ou passager_chauffeur
            }

            return $response;

        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
