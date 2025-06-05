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
        //ajouter les trajets proposés par l'utilisateur (chauffeur/passager_chauffeur)
        $trajets = $user->getTrajets();


        return $this->render('espace_utilisateur/utilisateur.html.twig', [
            'user' => $user,
            'vehicules' => $vehicules,
            'trajets' => $trajets,
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

    /** US10 un conducteur peut visionner ses trajets */

    #[Route('/espace-utilisateur/trajet/{id}', name: 'app_espace_trajet_detail')]
    public function showTrajetChauffeur(int $id, EntityManagerInterface $em): Response
    {
        $trajet = $em->getRepository(Trajet::class)->find($id);

        if (!$trajet) {
            throw $this->createNotFoundException('Trajet introuvable.');
        }

        $user = $this->getUser();

        if ($trajet->getChauffeur() !== $user) {
            $this->addFlash('danger', 'Accès non autorisé à ce trajet.');
            return $this->redirectToRoute('app_espace_utilisateur');
        }

        return $this->render('espace_utilisateur/detail-chauffeur.html.twig', [
            'trajet' => $trajet,
        ]);
    }

    /** US10 un conducteur peut annuler ses trajets */
    #[Route('/espace-utilisateur/annuler-trajet/{id}', name: 'app_espace_trajet_annuler', methods: ['POST'])]
    public function annulerTrajet(int $id, EntityManagerInterface $em): Response
    {
        $trajet = $em->getRepository(Trajet::class)->find($id);

        if (!$trajet) {
            $this->addFlash('danger', 'Trajet introuvable.');
            return $this->redirectToRoute('app_espace_utilisateur');
        }

        $user = $this->getUser();
        if (!$user || $trajet->getChauffeur() !== $user) {
            $this->addFlash('danger', 'Vous ne pouvez pas annuler ce trajet.');
            return $this->redirectToRoute('app_espace_utilisateur');
        }

        // Recréditer les participants
        foreach ($trajet->getParticipations() as $participation) {
            $passager = $participation->getUser();
            $passager->setCredits($passager->getCredits() + $trajet->getPrix());
            $em->remove($participation); // facultatif : ou laisser si cascade persist
        }

        $em->remove($trajet); // suppression définitive
        $em->flush();

        $this->addFlash('success', 'Le trajet a été annulé et les participants ont été recrédités.');
        return $this->redirectToRoute('app_espace_utilisateur');
    }

}
