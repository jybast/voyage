<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Article;
use App\Entity\Categorie;
use App\Form\ArticleType;
use App\Entity\Commentaire;
use App\Form\CategorieType;
use App\Form\CommentaireType;
// Nous appelons le bundle KNP Paginator
use App\Form\ChercheArticleType;
use App\Repository\ArticleRepository;
use Knp\Component\Pager\PaginatorInterface; 
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $donnees = $articleRepository->findBy([
            'valide' => true], [
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
        // Construction du formulaire
        $form = $this->createForm(ArticleType::class, $article);
        // Traitement des informations reçues
        $form->handleRequest($request);
     
        // Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) 
        {
            // On récupère les media transmis
            $medias = $form->get('media')->getData();
            // On boucle sur les images
            foreach($medias as $media)
            {
                // On génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $media->guessExtension();
                // On copie le fichier physique dans le dossier uploads
                $media->move(
                    $this->getParameter('media_directory'),
                    $fichier
                );
                // On stocke l'image dans la base de données (son nom)
                $img = new Media();
                $img->setNom($fichier);
                // on passe l'instance du media dans l'article
                $article->addMedium($img);

            }

            $entityManager = $this->getDoctrine()->getManager();
            // récupère l'auteur
            $article->setAuteur($this->getUser());

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
        ]);
    }

    /**
     * @Route("/{id}", name="article_lire", methods={"GET", "POST"})
     */
    public function lire(Request $request, Article $article): Response
    {
        // si aucun article trouvé
        if(!$article){
            throw $this->createNotFoundException("L\'article n\'a pas été trouvé");
        }
        
        // récupère tous les commentaires valides liés à cet article
        $commentaires = $this->getDoctrine()
                             ->getRepository(Commentaire::class)
                             ->findBy([
                                 'valide' => 1,
                                 'article' => $article
                             ]) ;

                    
        // Nouvelle instance de Commentaire, permet d'ajouter un commentaire
        $commentaire = new Commentaire(); 
        
        // Instance du formulaire pour le commentaire
        $form_commentaire = $this->createForm(CommentaireType::class, $commentaire);

        // Récupère les données passées au commentaire
        $form_commentaire->handleRequest($request);

        // Traitement du formulaire
        if($form_commentaire->isSubmitted() && $form_commentaire->isValid()){
            // Faire le lien entre le commentaire et l'article
            $commentaire->setArticle($article);
            $commentaire->setValide(false);
            // Intègre le commentaire à la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($commentaire);
            $em->flush();
        }

        // Rendre la vue avec les paramètres
        return $this->render('article/lire.html.twig', [
            'article' => $article,
            'form_commentaire' => $form_commentaire->createView(),
            'commentaires' => $commentaires,
        ]);
    }

    /**
     * @Route("/{id<\d+>}/modifier", name="article_modifier", methods={"GET","POST"})
     */
    public function modifier(Request $request, Article $article): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {

             // On récupère les media transmis
             $medias = $form->get('media')->getData();
             // On boucle sur les images
             foreach($medias as $media)
             {
                 // On génère un nouveau nom de fichier
                 $fichier = md5(uniqid()) . '.' . $media->guessExtension();
                 // On copie le fichier physique dans le dossier uploads
                 $media->move(
                     $this->getParameter('media_directory'),
                     $fichier
                 );
                 // On stocke l'image dans la base de données (son nom)
                 $img = new Media();
                 $img->setNom($fichier);
                 // on passe l'instance du media dans l'article
                 $article->addMedium($img);
 
             }
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
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) 
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_lister');
    }

    /**
     * @Route("/supprimer/media/{id}", name="media_supprimer", methods={"DELETE"})
     */
    public function supprimerMedia(Media $media, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        // On vérifie si le token est valide, qui s'appelle delete + id du media
        if($this->isCsrfTokenValid('delete'.$media->getId(), $data['_token']))
        {
            // On récupère le nom du media pour pouvoir le supprimer physiquement
            $nom = $media->getNom();
            // On supprime le fichier logique
            unlink($this->getParameter('media_directory').'/'.$nom);
            // On supprime l'entrée de la base
            $em = $this->getDoctrine()->getManager();
            $em->remove($media);
            $em->flush();
            // On répond en json
            return new JsonResponse(['success' => 1]);
        }
        else
        {
            // si le token n'est pas valide
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
    
}
