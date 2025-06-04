<?php

namespace App\Controller;

use App\Repository\VehiculeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

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
}
