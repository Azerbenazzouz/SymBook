<?php

namespace App\Form;

use App\Entity\Categories;
use App\Model\FilterData;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
            ->add('titre')
            ->add('auteur')
            ->add('categories', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'libelle',
                'multiple' => true,
                'expanded' => true,
                'label' => true,
                'required' => false,
            ])
            ->add('prixMin', NumberType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Prix min'
                ]
            ])
            ->add('prixMax', NumberType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Prix max'
                ]
            ])
            /*
            ->add('reset', ResetType::class, [
                'label' => 'Réinitialiser',
                'attr' => [
                    'class' => 'btn btn-secondary'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filtrer',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])*/
            ;
    }
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults([
            'data_class' => FilterData::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix(){
        return '';
    }
}