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
            $employe->setDateEmbauche($form->get('dateEmbauche')->getData());
            $employe->setCreatedAt(new \DateTimeImmutable());
            $employe->setUser($user);

            // Enregistrer en base
            $em->persist($user);
            $em->persist($employe);
            $em->flush();

            $this->addFlash('success', 'Employé créé avec succès.');
            return $this->redirectToRoute('app_espace_admin');
        }

        return $this->render('admin/new_employe.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
