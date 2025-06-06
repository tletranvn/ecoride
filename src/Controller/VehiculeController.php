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
//use Symfony\Component\Security\HttpFoundation\Attribute\IsGranted;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

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

            return $this->redirectToRoute('app_covoiturage'); // Rediriger vers la page de covoiturage
        }

        return $this->render('vehicule/new-vehicule.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
