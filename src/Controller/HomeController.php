<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/mentions-legales', name: 'app_mentions')]
    public function mentions(): Response
    {
        return $this->render('mentions-legales.html.twig');
    }

    #[Route('/rechercher-trajet', name: 'app_rechercher_trajet', methods: ['GET'])]
    public function rechercherTrajet(Request $request): Response
    {
        $villeDepart = $request->query->get('ville_depart');
        $villeArrivee = $request->query->get('ville_arrivee');

        // Pour l'instant, on affiche juste les valeurs saisies
        return $this->render('recherche/resultats.html.twig', [
            'villeDepart' => $villeDepart,
            'villeArrivee' => $villeArrivee,
        ]);
    }

}
