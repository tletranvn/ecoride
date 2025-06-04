<?php

namespace App\Controller;

use App\Form\ChoixProfilType;
use App\Entity\Trajet;
use App\Form\TrajetType;
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

    /** US9 saisir un voyage pour formulaire TrajetType */
    #[Route('/espace-utilisateur/nouveau-trajet', name: 'app_espace_trajet_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifier que l'utilisateur a le bon rôle
        if (!in_array($user->getUserType(), ['chauffeur', 'passager_chauffeur'])) {
            $this->addFlash('warning', 'Seuls les conducteurs peuvent ajouter un trajet.');
            return $this->redirectToRoute('app_espace_utilisateur');
        }

        $trajet = new Trajet();
        $form = $this->createForm(TrajetType::class, $trajet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Données calculées automatiquement
            $trajet->setChauffeur($user);
            $trajet->setPlacesRestantes($trajet->getPlacesTotal());
            $vehicule = $trajet->getVehicule();

            $isElec = strtolower($vehicule->getTypeEnergie()) === 'électrique';
            $trajet->setIsEcoCertifie($isElec);

            $trajet->setCreatedAt(new \DateTimeImmutable());

            // Déduire 2 crédits au chauffeur
            $user->setCredits($user->getCredits() - 2);

            $entityManager->persist($trajet);
            $entityManager->flush();

            $this->addFlash('success', 'Trajet ajouté avec succès.');
            return $this->redirectToRoute('app_espace_utilisateur');
        }

        return $this->render('espace_utilisateur/new-trajet.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
