<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use OpenApi\Annotations as OA;

#[Route('/api', name: 'app_api_')]
class SecurityController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer, // Inject any required services here
        private EntityManagerInterface $manager // pour persist et flush
    ) {
        // Constructor logic if needed
    }


    #[Route('/registration', name: 'registration', methods: ['POST'])]

    /** 
    * @OA\Post(
    *     path="/api/registration",
    *     summary="Inscription d'un nouvel utilisateur",
    *     @OA\RequestBody(
    *         required=true,
    *         description="Données de l'utilisateur à inscrire",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="pseudo", type="string", example="Jimmy"),
    *             @OA\Property(property="email", type="string", example="adresse@email.com"),
    *             @OA\Property(property="password", type="string", example="Mot de passe")
    *         )
    *     ),
    *     @OA\Response(
    *         response=201,
    *         description="Utilisateur inscrit avec succès",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="user", type="string", example="Nom d'utilisateur"),
    *             @OA\Property(property="apiToken", type="string", example="31a023e212f116124a36af14ea0c1c3806eb9378"),
    *             @OA\Property(property="roles", type="array", @OA\Items(type="string", example="ROLE_USER"))
    *         )
    *     )
    * )
    */
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(),User::class,'json');
        $user->setPseudo($user->getPseudo());
        $user->setEmail($user->getEmail());
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(['user' => $user->getUserIdentifier(), 'apiToken' => $user->getApiToken(), 'roles' => $user->getRoles()], Response::HTTP_CREATED);
    }

    #[Route('/register', name: 'app_register', methods: ['GET'])]
    public function registerForm(): Response
    {
    return $this->render('security/register.html.twig');
    }




    #[Route('/login', name: 'login', methods: ['POST'])] //à changer name:'app_api_login' si besoin

    /** 
    * @OA\Post(
    *     path="/api/login",
    *     summary="Connecter un utilisateur",
    *     @OA\RequestBody(
    *         required=true,
    *         description="Données de l'utilisateur à inscrire",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="pseudo", type="string", example="Jimmy"),
    *             @OA\Property(property="username", type="string", example="adresse@email.com"),
    *             @OA\Property(property="password", type="string", example="Mot de passe")
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Connexion réussi",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="user", type="string", example="Nom d'utilisateur"),
    *             @OA\Property(property="apiToken", type="string", example="31a023e212f116124a36af14ea0c1c3806eb9378"),
    *             @OA\Property(property="roles", type="array", @OA\Items(type="string", example="ROLE_USER"))
    *         )
    *     )
    * )
    */
        public function login(#[CurrentUser] ?User $user): JsonResponse
        {
            if (null === $user) {
                return new JsonResponse(['message' => 'Missing credentials'], Response::HTTP_UNAUTHORIZED);
            }
            // If the user is authenticated, you can return their information
            return new JsonResponse(['user' => $user->getUserIdentifier(), 'apiToken' => $user->getApiToken(), 'roles' => $user->getRoles()], Response::HTTP_OK);
        }



}
