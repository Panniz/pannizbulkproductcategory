<?php

namespace Panniz\BulkProductCategory\Form\Type;

use PrestaShopBundle\Form\Admin\Type\CategoryChoiceTreeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class CategoryCloneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('destination_category', CategoryChoiceTreeType::class, [
                'label' => 'Seleziona la categoria di destinazione',
                'multiple' => false,
                'required' => true,
            ])
            ->add('recursive', ChoiceType::class, [
                'label' => 'Clonare ricorsivamente le sottocategorie?',
                'choices' => [
                    'No' => false,
                    'Si' => true,
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'data' => false,
            ])
            ->add('clone_products', ChoiceType::class, [
                'label' => 'Associare i prodotti alle nuove categorie?',
                'choices' => [
                    'No' => false,
                    'Si' => true,
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'data' => false,
            ])
            ->add('selected_categories', HiddenType::class, [
                'required' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Clona Categorie',
                'attr' => [
                    'class' => 'btn-primary',
                ],
            ]);
    }
}
