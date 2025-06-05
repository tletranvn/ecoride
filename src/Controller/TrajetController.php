<?php

namespace App\Controller;

use App\Entity\Trajet;
use App\Entity\Participation;
use App\Entity\User;
use App\Form\TrajetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrajetController extends AbstractController
{
    #[Route('/trajet/{id}', name: 'trajet_detail')] // Route pour afficher le détail d'un trajet pour US5
    public function show($id,EntityManagerInterface $em,Request $request): Response {

        // Récupérer le trajet par son ID
        $trajet = $em->getRepository(Trajet::class)->find($id);

        // Vérifier si le trajet existe
        if (!$trajet) {
            throw $this->createNotFoundException('Trajet non trouvé.');
        }

        // récupérer les paramètres s'ils existent
        $villeDepart = $request->query->get('villeDepart');
        $villeArrivee = $request->query->get('villeArrivee');
        $date = $request->query->get('date');

        // rendre la vue avec les données du trajet
        return $this->render('trajet/detail.html.twig', [
            'trajet' => $trajet,
            'villeDepart' => $villeDepart,
            'villeArrivee' => $villeArrivee,
            'date' => $date,
        ]);
    }

    #[Route('/mes-trajets', name: 'trajet_mes_trajets')]
    public function mesTrajets(): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            $this->addFlash('warning', 'Veuillez vous connecter pour voir vos trajets.');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer les crédits de l'utilisateur connecté
        $credits = $user->getCredits(); 

        // Récupérer les participations de l'utilisateur
        $participations = $user->getParticipations();

        // rendre la vue avec les participations et les crédits
        return $this->render('trajet/mes-trajets.html.twig', [
            'participations' => $participations,
            'credits' => $credits,
            'user' => $user,
        ]);
    }

    /** US6 et US8 participer à un covoiturage */
    #[Route('/trajet/{id}/participer', name: 'trajet_participer', methods: ['POST'])]
    public function participer(int $id, EntityManagerInterface $em): Response // Participer à un trajet
    {
        // Récupérer le trajet par son ID
        $trajet = $em->getRepository(Trajet::class)->find($id);

        // Vérifier si le trajet existe
        if (!$trajet) {
            throw $this->createNotFoundException('Trajet non trouvé.');
        }
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour participer à un trajet.');
            return $this->redirectToRoute('app_login');
        }
        // Vérifier si il reste des places
        if ($trajet->getPlacesRestantes() <= 0) {
            $this->addFlash('warning', 'Ce trajet est complet.');
            return $this->redirectToRoute('trajet_detail', ['id' => $id]);
        }
        // Vérifier si l'utilisateur a suffisamment de crédits
        if ($user->getCredits() < $trajet->getPrix()) {
            $this->addFlash('warning', 'Crédits insuffisants pour participer à ce trajet.');
            return $this->redirectToRoute('trajet_detail', ['id' => $id]);
        }

        // Décrémente les crédits et les places restantes
        $user->setCredits($user->getCredits() - $trajet->getPrix());
        $trajet->setPlacesRestantes($trajet->getPlacesRestantes() - 1);

        // Créer une participation
        $participation = new Participation();
        $participation->setUser($user);
        $participation->setTrajet($trajet);
        $participation->setCreatedAt(new \DateTimeImmutable());

        // Mise à jour des relations inversées
        $user->addParticipation($participation);
        $trajet->addParticipation($participation);

        // Mettre à jour crédits et places
        $user->setCredits($user->getCredits() - $trajet->getPrix());
        $trajet->setPlacesRestantes($trajet->getPlacesRestantes() - 1);

        $em->persist($participation);
        $em->flush();


        // Message de confirmation
        $this->addFlash('success', 'Vous participez maintenant à ce trajet !');

        // Rediriger vers la page mes-trajets au lieu de revenir sur le détail
        return $this->redirectToRoute('trajet_mes_trajets');

    }

    // US10 : un passager peut annuler sa participation à un trajet
    #[Route('/trajet/{id}/annuler-participation', name: 'trajet_annuler_participation')]
    public function annulerParticipation(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $trajet = $em->getRepository(Trajet::class)->find($id);

        if (!$trajet || !$user) {
            throw $this->createNotFoundException('Trajet ou utilisateur non trouvé.');
        }

        // Trouver la participation du user pour ce trajet
        $participation = $em->getRepository(Participation::class)->findOneBy([
            'user' => $user,
            'trajet' => $trajet,
        ]);

        if (!$participation) {
            $this->addFlash('warning', 'Vous ne participez pas à ce trajet.');
            return $this->redirectToRoute('trajet_mes_trajets');
        }

        // Supprimer la participation
        $user->removeParticipation($participation);
        $trajet->removeParticipation($participation);

        // Récupérer les crédits et places
        $user->setCredits($user->getCredits() + $trajet->getPrix());
        $trajet->setPlacesRestantes($trajet->getPlacesRestantes() + 1);

        $em->remove($participation);
        $em->flush();

        $this->addFlash('success', 'Votre participation a été annulée.');
        return $this->redirectToRoute('trajet_mes_trajets');
    }

}
