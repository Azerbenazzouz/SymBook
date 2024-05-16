<?php

namespace App\Form;

use App\Model\SearchData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder
    ->add('q',TextType::class,[
        'attr'=> [
            'placeholder'=> 'Rechercher par titre'
        ]
    ]);
}

public function configureOption(OptionsResolver $resolver)
{
    $resolver->setDefaults([
        'data_class' => SearchData::class ,
        'method'=>'GET',
        'csrf_production' => false
        ]);

}
}