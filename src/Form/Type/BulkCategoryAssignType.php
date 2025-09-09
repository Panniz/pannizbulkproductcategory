<?php
namespace Panniz\BulkProductCategory\Form\Type;

use PrestaShopBundle\Form\Admin\Type\CategoryChoiceTreeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class BulkCategoryAssignType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categories', CategoryChoiceTreeType::class, [
                'multiple' => true
            ])
            ->add('product_ids', HiddenType::class, [
                'required' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Assign Categories',
                'attr' => [
                    'class' => 'btn-primary',
                ],
            ]);
    }
}