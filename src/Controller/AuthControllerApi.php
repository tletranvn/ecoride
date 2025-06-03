<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthControllerApi extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET'])]
    public function registerForm(): Response
    {
        return $this->render('security/register.html.twig');
        
    }

    #[Route('/login', name: 'app_login', methods: ['GET'])]
    public function loginForm(): Response
    {
        return $this->render('security/login.html.twig');
    }
}


