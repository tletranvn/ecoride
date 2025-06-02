<?php

namespace App\Controller;

use App\Entity\Trajet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrajetController extends AbstractController
{
    #[Route('/trajet/{id}', name: 'trajet_detail')]
public function show(
    int $id,
    EntityManagerInterface $em,
    Request $request
): Response {
    $trajet = $em->getRepository(Trajet::class)->find($id);

    if (!$trajet) {
        throw $this->createNotFoundException('Trajet non trouvé.');
    }

    // récupérer les paramètres s'ils existent
    $villeDepart = $request->query->get('villeDepart');
    $villeArrivee = $request->query->get('villeArrivee');
    $date = $request->query->get('date');

    return $this->render('trajet/detail.html.twig', [
        'trajet' => $trajet,
        'villeDepart' => $villeDepart,
        'villeArrivee' => $villeArrivee,
        'date' => $date,
    ]);
}

}
