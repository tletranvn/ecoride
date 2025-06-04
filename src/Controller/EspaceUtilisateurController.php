<?php

namespace App\Controller;

use App\Form\ChoixProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\VehiculeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_USER')]
class EspaceUtilisateurController extends AbstractController
{
    #[Route('/espace-utilisateur', name: 'app_espace_utilisateur')]
    public function index(VehiculeRepository $vehiculeRepository): Response
    {
        $user = $this->getUser();
        $vehicules = $vehiculeRepository->findBy(['utilisateur' => $user]);

        return $this->render('espace_utilisateur/utilisateur.html.twig', [
            'user' => $user,
            'vehicules' => $vehicules,
        ]);
    }

    // Route pour choisir le profil de l'utilisateur US8
    // Cette route permet à l'utilisateur de choisir son rôle (passager, chauffeur, passager + chauffeur)
    // Le formulaire est géré par le type ChoixProfilType
    // Après la soumission du formulaire, le rôle de l'utilisateur est mis à jour dans la base de données
    #[Route('/espace-utilisateur/choix-profil', name: 'app_choix_profil')]
    public function choixProfil(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChoixProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Votre rôle a été mis à jour.');
            return $this->redirectToRoute('app_espace_utilisateur');
        }

        return $this->render('espace_utilisateur/choix-profil.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
