<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

//US13 admin créer un employé
use App\Entity\Employe;
use App\Entity\User;
use App\Form\EmployeTypeForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

//US13 admin voir les graphiques/statistiques
use App\Entity\Trajet;
use App\Entity\Participation;
//US13 voir/suspendre un compte
use App\Repository\UserRepository;
//US13 toggle user status
use Symfony\Component\HttpFoundation\RedirectResponse;


final class AdminController extends AbstractController
{
    #[Route('/espace-admin', name: 'app_espace_admin')]
    public function index(): Response
    {
        // Vérifier si l'utilisateur est connecté et a le rôle d'administrateur
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        // Connection de administrateur
        
        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    //US13 : admin peut créer un employé
    #[Route('/espace-admin/creer-employe', name: 'app_espace_admin_creer_employe')]
    public function newEmploye(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response 
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(EmployeTypeForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créer un User
            $user = new User();
            $user->setEmail($form->get('email')->getData());
            $user->setPseudo($form->get('pseudo')->getData());
            $user->setRoles(['ROLE_EMPLOYE']);
            $user->setCredits(0);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setApiToken(bin2hex(random_bytes(20)));

            // Encoder le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($hashedPassword);

            // Créer un Employe et lier au User
            $employe = new Employe();
            $employe->setPoste($form->get('poste')->getData());
            //$employe->setDateEmbauche($form->get('dateEmbauche')->getData());
            $employe->setCreatedAt(new \DateTimeImmutable());
            $employe->setUser($user);
            //le champ dateEmbauche peut être une chaîne de caractères ou un objet DateTime
            $dateString = $form->get('dateEmbauche')->getData();
                if (is_string($dateString)) {
                    $dateEmbauche = new \DateTimeImmutable($dateString);
                } else {
                    $dateEmbauche = \DateTimeImmutable::createFromMutable($dateString);
                }
            // Assurez-vous que $dateEmbauche est une instance de \DateTimeImmutable
            if (!$dateEmbauche instanceof \DateTimeImmutable) {
                throw new \InvalidArgumentException('La date d\'embauche doit être une instance de DateTimeImmutable.');
            }

            $employe->setDateEmbauche($dateEmbauche);


            // Enregistrer en base
            $em->persist($user);
            $em->persist($employe);
            $em->flush();

            $this->addFlash('success', 'Employé créé avec succès.');
            return $this->redirectToRoute('app_espace_admin_creer_employe');
        }

        return $this->render('admin/new_employe.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    //US13 : voir les graphiques/statistiques
    #[Route('/espace-admin/statistiques', name: 'app_espace_admin_statistiques')]
    public function statistiques(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $connection = $em->getConnection();

        // 1. Nombre de trajets par jour
        $trajetsParJour = $connection->executeQuery(
            "SELECT DATE(date_depart) as jour, COUNT(id) as total
            FROM trajet
            GROUP BY jour
            ORDER BY jour ASC"
        )->fetchAllAssociative();

        // 2. Crédits utilisés par jour
        $creditsParJour = $connection->executeQuery(
            "SELECT DATE(created_at) as jour, SUM(credits_utilises) as total
            FROM participation
            WHERE credits_utilises IS NOT NULL
            GROUP BY jour
            ORDER BY jour ASC"
        )->fetchAllAssociative();

        // 3. Total des crédits
        $totalCredits = $connection->executeQuery(
            "SELECT SUM(credits_utilises) FROM participation"
        )->fetchOne();

        return $this->render('admin/statistiques.html.twig', [
            'trajetsParJour' => $trajetsParJour,
            'creditsParJour' => $creditsParJour,
            'totalCredits' => $totalCredits,
        ]);
    }

    //US13 : voir/suspendre un compte
    #[Route('/espace-admin/comptes', name: 'app_espace_admin_comptes')]
    public function comptes(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $userRepository->findAll();

        return $this->render('admin/comptes.html.twig', [
            'users' => $users,
        ]);
    }

    //US13 : toggle user status (actif/inactif)
    #[Route('/espace-admin/toggle-user/{id}', name: 'app_admin_toggle_user_status')]
    public function toggleStatus(User $user, EntityManagerInterface $em): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user->setIsActive(!$user->isActive());
        $em->flush();

        $message = $user->isActive()
            ? 'Le compte de ' . $user->getPseudo() . ' a été réactivé ✅'
            : 'Le compte de ' . $user->getPseudo() . ' a été suspendu 🚫';

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_espace_admin_comptes');
    }
}
