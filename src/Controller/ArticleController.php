<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Form\ArticleType;
use App\Entity\Commentaire;
use App\Form\CategorieType;
use App\Form\CommentaireType;
use App\Repository\ArticleRepository;
use Knp\Component\Pager\PaginatorInterface; 
use Symfony\Component\HttpFoundation\Request;
// Nous appelons le bundle KNP Paginator
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * 
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * Lister tous les articles
     * @Route("/", name="article_lister", methods={"GET"})
     */
    public function lister(Request $request, ArticleRepository $articleRepository, PaginatorInterface $paginator): Response
    {
        // récupère les articles pour la gestion de la pagination
        $donnees = $articleRepository->findBy(['valide' => true], [
             
            'publierAt' => 'desc'
            ]);
        // Mise en place de la pagination
        $articles = $paginator->paginate(
            // Données à paginer, nos articles
            $donnees,
            // page en cours donnée dans l'Url, 1 par défaut
            $request->query->getInt('page', 1),
            // nombre d'éléments à afficher par page
            5
        );
        // Rendre la vue avec les paramètres
        return $this->render('article/lister.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/ajouter", name="article_ajouter", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        // Nouvelle instance d'article
        $article = new Article();

        // Nouvelle instance de Categorie
        $categorie = new Categorie();

         // Instance du formulaire pour la categorie
         $form_categorie = $this->createForm(CategorieType::class, $categorie);

         // Récupère les données passées a la categorie
         $form_categorie->handleRequest($request);
 
         // Traitement du formulaire
         if($form_categorie->isSubmitted() && $form_categorie->isValid()){
             // Faire le lien entre le categorie et l'article
            
             // Intègre le categorie à la base
             $em = $this->getDoctrine()->getManager();
             $em->persist($categorie);
             $em->flush();
         }



        // Construction du formulaire
        $form = $this->createForm(ArticleType::class, $article);

        // Traitement des informations reçues
        $form->handleRequest($request);

        // Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $article->addCategory($categorie);

            $entityManager->persist($article);
            $entityManager->flush();

            // message d'information
            $this->addFlash('message', 'Votre article va être validé avant publication.');
            // renvoie sur la page
            return $this->redirectToRoute('article_lister');
        }

        // si tout n'est pas correct
        return $this->render('article/ajouter.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
            'form_categorie' => $form_categorie->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="article_lire", methods={"GET", "POST"})
     */
    public function lire(Request $request, Article $article): Response
    {
        if(!$article){
            throw $this->createNotFoundException("L\'article n\'a pas été trouvé");
        }


        // récupère tous les commentaires liés à cet article
        $commentaires = $this->getDoctrine()
                             ->getRepository(Commentaire::class)
                             ->findBy([
                                 'valide' => 1,
                                 'article' => $article
                             ]) ;
        // Nouvelle instance de Commentaire, permet d'ajouter un categorie
        $categorie = new Commentaire(); 
        
        // Instance du formulaire pour le categorie
        $form_categorie = $this->createForm(CommentaireType::class, $categorie);

        // Récupère les données passées au categorie
        $form_categorie->handleRequest($request);

        // Traitement du formulaire
        if($form_categorie->isSubmitted() && $form_categorie->isValid()){
            // Faire le lien entre le categorie et l'article
            $categorie->setArticle($article);
            $categorie->setValide(false);
            // Intègre le categorie à la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($categorie);
            $em->flush();
        }

        // Rendre la vue avec les paramètres
        return $this->render('article/lire.html.twig', [
            'article' => $article,
            'form_categorie' => $form_categorie->createView(),
            'commentaires' => $commentaires,
            
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="article_modifier", methods={"GET","POST"})
     */
    public function modifier(Request $request, Article $article): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('article_lister');
        }

        return $this->render('article/modifier.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_supprimer", methods={"DELETE"})
     */
    public function supprimer(Request $request, Article $article): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_lister');
    }
}
