<?php

namespace App\Form;

use App\Entity\Motcle;
use App\Entity\Article;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de l\'article',
            ])
            ->add('soustitre', TextType::class, [
                'label' => 'Sous-Titre de l\'article',
            ])
            ->add('contenu', CKEditorType::class, [
                'label' => 'Texte de l\'article.',
            ])
            ->add('auteur')
            ->add('media', FileType::class, [
                'label' => false,
                'multiple' => true,
                'mapped' => false,
                'required' => false
            ])
            ->add('motcles', EntityType::class, [
                'label' => 'Mots clés',
                'class' => Motcle::class,
                'multiple' => true,
                'expanded' => true
            ])
            ->add('categories', EntityType::class, [
                'label' => 'Catégories',
                'class' => Categorie::class,
                'multiple' => true,
                //'expanded' => true
            ])
            ->add('Enregistrer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
