<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controleur des pages génériques de l'application
 */
class PageController extends AbstractController
{
    /**
     * @Route("/accueil", name="blog_accueil")
     */
    public function accueil()
    {
        return $this->render('page/accueil.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    /**
     * @Route("/contact", name="blog_contact")
     */
    public function contact()
    {
        return $this->render('page/contact.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    /**
     * @Route("/profile", name="blog_profile")
     */
    public function profile()
    {
        return $this->render('page/profile.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    /**
     * @Route("/mentions", name="blog_mentions")
     */
    public function mentions()
    {
        return $this->render('page/mentions.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
}
