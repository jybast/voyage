<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ChercheArticleType;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Controleur des pages génériques de l'application
 */
class PageController extends AbstractController
{
    /**
     * @Route("/accueil", name="blog_accueil")
     */
    public function accueil(ArticleRepository $articleRepository, Request $request)
    {
         // récupère les derniers articles publiés
        // $actualites = $repository->findBylastCreated();
        $actualites = $articleRepository->findBy(['valide' => true], ['publierAt' => 'DESC'], 3);
        // formulaire de recherche
        $form = $this->createForm(ChercheArticleType::class);
        // traitement des donéées passées dans le formulaire de recherche
        $search = $form->handleRequest($request);
       
        $articles = $actualites;
        
        if($form->isSubmitted() && $form->isValid()){
            // ON recherche les articles correspondant aux critères de recherche
            $articles = $articleRepository->search(
                $search->get('mots')->getData(),
                $search->get('categorie')->getData()
            );
        }

        return $this->render('page/accueil.html.twig', [
            'actualites' => $actualites,
            'form' => $form->createView(),
            'articles' => $articles
        ]);
    }

     /**
     * @Route("/rechercher", name="rechercher")
     */
    public function rechercher(Request $request, ArticleRepository $articleRepository)
    {
       // Je récupère mon dernier article pour un affichage au cas rien ne correspond à la recherche
        //$donnees = $articleRepository->findBy(['valide' => true], ['publierAt' => 'DESC'], 3);
        $donnees = new Article();
        // formulaire de recherche
         $form = $this->createForm(ChercheArticleType::class);
         // traitement des données passées dans le formulaire de recherche
         $search = $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // ON recherche les articles correspondant aux critères de recherche
            $donnees = $articleRepository->search(
                // infos passées dans le formulaire
                $search->get('mots')->getData(),
                $search->get('categorie')->getData()
            );
        }

        return $this->render('article/rechercher.html.twig', [
            'donnees' => $donnees,
            'form' => $form->createView(),
            //'article' => $article 
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
     * @Route("/profil", name="blog_profil")
     */
    public function profil(Request $request)
    {
        // je récupère l'utilisateur connecté
        $user = $this->getUser();
     
        return $this->render('user/profil.html.twig', [
            'user' => $user
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
