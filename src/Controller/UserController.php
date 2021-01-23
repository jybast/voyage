<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserProfilType;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/user", name="user_")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="liste", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
         return $this->render('user/liste.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/ajouter", name="ajouter", methods={"GET","POST"})
     */
    public function ajouter(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_liste');
        }

        return $this->render('user/ajouter.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/profil", name="profil", methods={"GET","POST"})
     */
    public function profil(User $user): Response
    {
        return $this->render('user/profil.html.twig', [
            'user' => $user,
        ]);
    }


    /**
     * @Route("/{id}/modifierProfil", name="profil_modifier", methods={"GET","POST"})
     */
    public function modifierProfil(Request $request, User $user): Response
    {
        $form = $this->createForm(UserProfilType::class, $user);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('blog_accueil');
        }

        return $this->render('user/modifierProfil.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/activer/{id}", name="activer")
     */
    public function activer(User $user)
    {
        $user->setActif(($user->getActif())?false:true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Le profil a été activé.');

         // Rendre la vue avec les paramètres
        return $this->render('user/profil.html.twig', [
        'user' => $user,
    ]);

        return new Response("true");
    }



     /**
     * @Route("/{id}/listerArticlesProfil", name="profil_articles", methods={"GET","POST"})
     */
    public function listerArticlesProfil(Request $request, User $user, ArticleRepository $articleRepository, PaginatorInterface $paginator): Response
    {
       // récupère les articles pour la gestion de la pagination
       $donnees = $articleRepository->findBy(
           ['valide' => true],
           ['publierAt' => 'desc'],
           ['auteur' => $user ]
        );
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
    return $this->render('user/articlesProfilListe.html.twig', [
        'articles' => $articles,
    ]);
    }

}
