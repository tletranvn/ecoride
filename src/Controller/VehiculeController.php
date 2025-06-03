<?php


namespace App\Controller;

use App\Entity\Vehicule;
use App\Entity\User;
use App\Form\VehiculeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_USER')]
class VehiculeController extends AbstractController
{
    #[Route('/vehicule/nouveau', name: 'app_vehicule_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vehicule = new Vehicule();
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vehicule->setUtilisateur($this->getUser());
            $vehicule->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($vehicule);
            $entityManager->flush();

            $this->addFlash('success', 'Véhicule ajouté avec succès.');

            return $this->redirectToRoute('app_espace_utilisateur'); 
        }

        return $this->render('vehicule/new-vehicule.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
