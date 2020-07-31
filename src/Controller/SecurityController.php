<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="blog_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si authentification est correcte, renvoie sur la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('blog_accueil');
        }

        // Si erreur
        $error = $authenticationUtils->getLastAuthenticationError();
        // dernier identifiant entré 
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="blog_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
