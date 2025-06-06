<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; 
use Doctrine\ORM\EntityManagerInterface;
//use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\SecurityBundle\Security;

use App\Entity\Participation;
use App\Entity\Trajet;
use App\Entity\Vehicule;

final class CovoiturageController extends AbstractController
{
    #[Route('/covoiturages', name: 'app_covoiturage')]
    public function index(EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // trajets réservés (passager)
        $participation = $entityManager->getRepository(Participation::class)->findBy(['user' => $user]);

        // trajets proposés (chauffeur)
        $trajetsProposes = $entityManager->getRepository(Trajet::class)->findBy(['chauffeur' => $user]);

        // véhicules du chauffeur
        $vehicules = $entityManager->getRepository(Vehicule::class)->findBy(['utilisateur' => $user]);

        return $this->render('covoiturage/covoiturage.html.twig', [
            'user' => $user,
            'participation' => $participation,
            'trajetsProposes' => $trajetsProposes,
            'vehicules' => $vehicules,
        ]);
    }

}
