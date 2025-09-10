<?php
// in src/Form/Type/BulkCategoryAssignType.php
namespace Panniz\BulkProductCategory\Form\Type;

use PrestaShopBundle\Form\Admin\Type\CategoryChoiceTreeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; // <-- Importa ChoiceType
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class BulkCategoryAssignType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('action', ChoiceType::class, [
                'label' => 'Azione da eseguire',
                'choices' => [
                    'Add selected categories' => 'add',
                    'Remove selected categories' => 'remove',
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'data' => 'add',
            ])
            ->add('categories', CategoryChoiceTreeType::class, [
                'multiple' => true
            ])
            ->add('product_ids', HiddenType::class, [
                'required' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Submit',
                'attr' => [
                    'class' => 'btn-primary',
                ],
            ]);
    }
}