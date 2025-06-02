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

        // MODIF : récupération des filtres depuis le formulaire GET
        $prixMax = $request->query->get('prixMax');
        $noteMin = $request->query->get('noteMin');
        $eco = $request->query->getBoolean('eco');
        $dureeMax = $request->query->get('dureeMax');


        $trajets = [];
        $alternativeDate = null;

        if ($villeDepart && $villeArrivee && $date) {
            $dateObj = new \DateTimeImmutable($date);

            // MODIF : création du QueryBuilder
            $qb = $trajetRepository->createQueryBuilder('t')
                ->join('t.chauffeur', 'u') // pour pouvoir filtrer sur la note plus tard
                ->andWhere('t.villeDepart = :depart')
                ->andWhere('t.villeArrivee = :arrivee')
                ->andWhere('t.dateDepart BETWEEN :start AND :end')
                ->setParameter('depart', $villeDepart)
                ->setParameter('arrivee', $villeArrivee)
                ->setParameter('start', $dateObj->setTime(0, 0))
                ->setParameter('end', $dateObj->setTime(23, 59, 59));

            // MODIF : ajout du filtre écologique
            if ($eco) {
                $qb->andWhere('t.isEcoCertifie = true');
            }

            // MODIF : ajout du filtre sur le prix maximum
            if ($prixMax !== null && is_numeric($prixMax)) {
                $qb->andWhere('t.prix <= :prixMax')
                   ->setParameter('prixMax', $prixMax);
            }

            // MODIF : ajout du filtre sur la note minimale du chauffeur
            if ($noteMin !== null && is_numeric($noteMin)) {
                $qb->andWhere('(SELECT AVG(a.note) FROM App\Entity\Avis a WHERE a.cible = u) >= :noteMin')
                   ->setParameter('noteMin', $noteMin);
            }

            // MODIF : ajout du filtre sur la durée maximale du trajet
            if ($dureeMax !== null && is_numeric($dureeMax)) {
                $qb->andWhere('t.duree <= :dureeMax')
                    ->setParameter('dureeMax', $dureeMax);
            }

            $trajets = $qb->getQuery()->getResult();        

            // recherche du prochain trajet si aucun trouvé
            if (count($trajets) === 0) {
                $alternative = $trajetRepository->createQueryBuilder('t')
                    ->andWhere('t.villeDepart = :depart')
                    ->andWhere('t.villeArrivee = :arrivee')
                    ->andWhere('t.dateDepart > :date')
                    ->orderBy('t.dateDepart', 'ASC')
                    ->setMaxResults(1)
                    ->setParameter('depart', $villeDepart)
                    ->setParameter('arrivee', $villeArrivee)
                    ->setParameter('date', $dateObj)
                    ->getQuery()
                    ->getOneOrNullResult();

                if ($alternative) {
                    $alternativeDate = $alternative->getDateDepart();
                    $trajets = [$alternative];
                }
            }
        }

        return $this->render('recherche/resultats.html.twig', [
            'villeDepart' => $villeDepart,
            'villeArrivee' => $villeArrivee,
            'date' => $date,
            'trajets' => $trajets,
            'alternativeDate' => $alternativeDate,
        ]);
    }
}
