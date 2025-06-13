<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
//pour methode avisEnAttente
use App\Entity\Avis;
use Doctrine\ORM\EntityManagerInterface;
//pour methode validerAvis et refuserAvis
use Symfony\Component\HttpFoundation\RedirectResponse;


final class EmployeController extends AbstractController
{
    #[Route('/espace-employe', name: 'app_espace_employe')]
    public function index(): Response
    {
        //vérifier si l'utilisateur est connecté et a le rôle d'employé
      
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        return $this->render('employe/employe.html.twig', [
            'controller_name' => 'EmployeController',
        ]);
    }

    // Route pour lister les avis en attente de validation
    #[Route('/espace-employe/avis', name: 'app_espace_employe_avis')]
    public function avisEnAttente(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $avisEnAttente = $em->getRepository(Avis::class)->findBy([
            'isValidated' => false
        ]);

        return $this->render('employe/avis_en_attente.html.twig', [
            'avisEnAttente' => $avisEnAttente,
        ]);
    }

    // Route pour valider un avis
    #[Route('/espace-employe/avis/{id}/valider', name: 'app_espace_employe_avis_valider')]
    public function validerAvis(Avis $avis, EntityManagerInterface $em): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $avis->setIsValidated(true);
        $em->flush();

        $this->addFlash('success', 'Avis validé avec succès.');
        return $this->redirectToRoute('app_espace_employe_avis');
    }

    // Route pour refuser un avis
    #[Route('/espace-employe/avis/{id}/refuser', name: 'app_espace_employe_avis_refuser')]
    public function refuserAvis(Avis $avis, EntityManagerInterface $em): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $em->remove($avis);
        $em->flush();

        $this->addFlash('danger', 'Avis refusé et supprimé.');
        return $this->redirectToRoute('app_espace_employe_avis');
    }

    // Route pour gérer les trajets mal passés (notes faibles, problèmes signalés, etc.)
    #[Route('/espace-employe/trajets-problemes', name: 'app_espace_employe_trajets_problemes')]
    public function trajetsProblemes(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $avisProblemes = $em->getRepository(Avis::class)->createQueryBuilder('a')
            ->where('a.isValidated = true')
            ->andWhere('a.note <= 2')
            ->getQuery()
            ->getResult();

        return $this->render('employe/trajets_problemes.html.twig', [
            'avisProblemes' => $avisProblemes,
        ]);
    }

}
