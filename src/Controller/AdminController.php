<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

 /**
     * @Route("/admin", name="admin_")
     */
class AdminController extends AbstractController
{
    /**
     * Page d'accueil du système d'administration
     * 
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/admin/categorie/ajout", name="categories_ajout")
     */
    public function ajoutCategorie(Request $request)
    {
        $categorie = new Categorie;

        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($categorie);
            $em->flush();

            return $this->redirectToRoute('admin_home');
        }

        return $this->render('admin/categorie/ajout.html.twig', [
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/modifier/{id}", name="modifier")
     */
    public function ModifCategorie(Categorie $categorie, Request $request)
    {
        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($categorie);
            $em->flush();

            return $this->redirectToRoute('admin_categories_home');
        }

        return $this->render('admin/categories/ajout.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
