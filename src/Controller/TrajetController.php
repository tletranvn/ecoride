<?php

namespace App\Controller;

use App\Entity\Trajet;
use App\Entity\Participation;
use App\Entity\User;
use App\Entity\Avis;
use App\Form\TrajetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// US10 pour envoi d'email
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TrajetController extends AbstractController
{
    #[Route('/trajet/{id}', name: 'trajet_detail')] // Route pour afficher le détail d'un trajet pour US5
    public function showTrajetPassager($id,EntityManagerInterface $em,Request $request): Response {

        // Récupérer le trajet par son ID
        $trajet = $em->getRepository(Trajet::class)->find($id);

        // Vérifier si le trajet existe
        if (!$trajet) {
            throw $this->createNotFoundException('Trajet non trouvé.');
        }

        // récupérer les paramètres s'ils existent
        $id = $request->query->get('id'); // pour US12 l'employé peut voir le détail d'un trajet
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

    //#[Route('/mes-trajets', name: 'trajet_mes_trajets')] ne plus besoin, tout centralisé dans CovoiturageController
    //public function mesTrajets(): Response
    //{
    //    // Récupérer l'utilisateur connecté
    //    $user = $this->getUser();

    //    // Vérifier si l'utilisateur est connecté
    //    if (!$user) {
    //        $this->addFlash('warning', 'Veuillez vous connecter pour voir vos trajets.');
    //        return $this->redirectToRoute('app_login');
    //    }

    //    // Récupérer les crédits de l'utilisateur connecté
    //   $credits = $user->getCredits(); 

    //    // Récupérer les participations de l'utilisateur
    //    $participations = $user->getParticipations();

    //    // rendre la vue avec les participations et les crédits
    //    return $this->render('trajet/mes-trajets.html.twig', [
    //        'participations' => $participations,
    //        'credits' => $credits,
    //        'user' => $user,
    //    ]);
    //}

    /** US6 et US8 participer à un covoiturage + US10 envoyer un email à passager quand annuler un trajet réservé*/
    #[Route('/trajet/{id}/participer', name: 'trajet_participer', methods: ['POST'])]
    public function participer(int $id, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        // Récupérer le trajet concerné
        $trajet = $em->getRepository(Trajet::class)->find($id);

        // Vérifier que le trajet existe
        if (!$trajet) {
            throw $this->createNotFoundException('Trajet non trouvé.');
        }

        // Récupérer l'utilisateur connecté
        /** @var \App\Entity\User $user */ // On utilise le type hinting pour indiquer que l'utilisateur est de type User pour Intellephense
        $user = $this->getUser();

        // Vérifier que l'utilisateur est connecté
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour participer à un trajet.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier qu’il reste des places
        if ($trajet->getPlacesRestantes() <= 0) {
            $this->addFlash('warning', 'Ce trajet est complet.');
            return $this->redirectToRoute('trajet_detail', ['id' => $id]);
        }

        // Vérifier que l'utilisateur a assez de crédits
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
        $participation->setCreditsUtilises($trajet->getPrix()); // Enregistrer le montant des crédits utilisés US10

        // Ajout aux relations inversées
        $user->addParticipation($participation);
        $trajet->addParticipation($participation);

        // Enregistrer en base de données
        $em->persist($participation);
        $em->flush();

        // --- ENVOI DE L'EMAIL ---
        $email = (new Email())
            ->from('no-reply@ecoride.fr') // Adresse de l'expéditeur
            ->to($user->getEmail())       // Email du passager
            ->subject('Confirmation de participation au trajet EcoRide')
            ->html("
                <p>Bonjour <strong>{$user->getPseudo()}</strong>,</p>
                <p>Merci pour votre réservation sur EcoRide !</p>
                <p>Vous participez maintenant au trajet :</p>
                <ul>
                    <li><strong>Trajet :</strong> {$trajet->getVilleDepart()} → {$trajet->getVilleArrivee()}</li>
                    <li><strong>Date :</strong> {$trajet->getDateDepart()->format('d/m/Y H:i')}</li>
                    <li><strong>Prix :</strong> {$trajet->getPrix()} crédits</li>
                    <li><strong>Conducteur :</strong> {$trajet->getChauffeur()->getPseudo()}</li>
                </ul>
                <p>Bon voyage avec EcoRide !</p>
            ");

        $mailer->send($email); // Envoie effectif

        // Message flash de confirmation
        $this->addFlash('success', 'Vous participez maintenant à ce trajet ! Un email de confirmation vous a été envoyé.');

        // Redirection vers "mes trajets"
        return $this->redirectToRoute('app_covoiturage');
    }


    // US10 : un passager peut annuler sa participation à un trajet
    #[Route('/trajet/{id}/annuler-participation', name: 'trajet_annuler_participation')]
    public function annulerParticipation(int $id, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
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
            return $this->redirectToRoute('app_covoiturage');
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
        return $this->redirectToRoute('app_covoiturage');
    }

    //US11 : un passager peut valider un trajet terminé

    //A. voir tous les trajets à valider
    #[Route('/mes-validations', name: 'mes_validations')]
    public function mesValidations(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Rechercher les participations à des trajets terminés mais non encore validées
        $participations = $em->getRepository(Participation::class)->findBy([
            'user' => $user,
            'trajetValide' => null, // pas encore validé
        ]);

        return $this->render('trajet/mes-validations.html.twig', [
            'participations' => $participations
        ]);
    }

    // B. valider ou signaler un trajet
    #[Route('/trajet/{id}/valider-participation', name: 'trajet_valider_participation', methods: ['POST'])]
    public function validerParticipation(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $participation = $em->getRepository(Participation::class)->find($id);

        if (!$participation || $participation->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $isValide = $request->request->get('valider') === '1';
        $commentaire = $request->request->get('commentaire');

        $participation->setTrajetValide($isValide);
        $participation->setCommentaire($commentaire);

        if (!$isValide) {
            // Créer un avis bloqué si problème signalé
            $avis = new Avis();
            $avis->setAuteur($user);
            $avis->setCible($participation->getTrajet()->getChauffeur());
            $avis->setTrajet($participation->getTrajet());
            $avis->setNote(0); // ou null selon ta logique
            $avis->setCommentaire($commentaire);
            $avis->setCreatedAt(new \DateTimeImmutable());
            $avis->setIsValidated(false); // à valider plus tard par un employé

            $em->persist($avis);
        }

        if ($isValide) {
            $chauffeur = $participation->getTrajet()->getChauffeur();
            $chauffeur->setCredits($chauffeur->getCredits() + $participation->getCreditsUtilises());
        }

        $em->flush();

        $this->addFlash('success', $isValide ? 'Merci pour votre validation.' : 'Votre signalement a bien été enregistré.');
        return $this->redirectToRoute('mes_validations');
    }

}
