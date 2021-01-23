<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ChercheArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mots', SearchType::class, [
                'label' => 'par mot clé ou expression',
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => false
            ])
            ->add('categorie', EntityType::class, [
                'label' => 'par catégorie',
                'class' => Categorie::class,
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => false
            ])
            ->add('Rechercher', SubmitType::class, [
                   'attr' => [
                    'class' => 'btn btn-lg btn-success mt-3',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
