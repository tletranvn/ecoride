<?php

namespace App\Controller;

use App\Repository\TrajetRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RechercheController extends AbstractController
{
    #[Route('/recherche', name: 'app_recherche')]
    public function index(): Response
    {
        return $this->render('recherche/index.html.twig', [
            'controller_name' => 'RechercheController',
        ]);
    }

    #[Route('/recherche/resultats', name: 'app_recherche_resultats', methods: ['GET'])]
    public function resultats(Request $request, TrajetRepository $trajetRepository): Response
    {
        $villeDepart = $request->query->get('villeDepart');
        $villeArrivee = $request->query->get('villeArrivee');
        $date = $request->query->get('date');

        $trajets = [];

        if ($villeDepart && $villeArrivee && $date) {
            $dateObj = new \DateTimeImmutable($date);

            $trajets = $trajetRepository->createQueryBuilder('t')
                ->andWhere('t.villeDepart = :depart')
                ->andWhere('t.villeArrivee = :arrivee')
                ->andWhere('t.dateDepart BETWEEN :start AND :end')
                ->setParameter('depart', $villeDepart)
                ->setParameter('arrivee', $villeArrivee)
                ->setParameter('start', $dateObj->setTime(0, 0))
                ->setParameter('end', $dateObj->setTime(23, 59, 59))
                ->getQuery()
                ->getResult();
        }

        return $this->render('recherche/resultats.html.twig', [
            'villeDepart' => $villeDepart,
            'villeArrivee' => $villeArrivee,
            'date' => $date,
            'trajets' => $trajets,
        ]);
    }
}
