<?php

namespace Panniz\BulkProductCategory\Controller\Admin;

use Doctrine\DBAL\Connection;
use Panniz\BulkProductCategory\Form\Type\BulkCategoryAssignType;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class BulkAssignCategoryController extends FrameworkBundleAdminController
{
    public function __construct(
        private Connection $connection,
        private TranslatorInterface $translator,
        private LegacyContext $legacyContext
    ) {
        $this->connection = $connection;
        $this->translator = $translator;
    }

    /**
     * The index method signature is now simpler.
     */
    public function index(Request $request): Response
    {
        $productIds = $request->get('product_bulk', []);
        $productIdsAsString = implode(',', $productIds);

        $form = $this->createForm(BulkCategoryAssignType::class, ['product_ids' => $productIdsAsString]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $submittedProductIds = explode(',', $data['product_ids']);
            $categoryIds = $data['categories'];

            // We use $this->connection and $this->translator that we received in the constructor
            $this->assignProductsToCategories($submittedProductIds, $categoryIds);

            $this->addFlash('success', $this->translator->trans(
                'Categories successfully updated for %count% products.',
                ['%count%' => count($submittedProductIds)],
                'Admin.Notifications.Success'
            ));

            return $this->redirectToRoute('admin_products_index');
        }

        $cancelUrl = $this->legacyContext->getContext()->link->getAdminLink('AdminProducts');

        return $this->render('@Modules/pannizbulkproductcategory/views/templates/admin/bulk_assign_form.html.twig', [
            'layoutTitle' => $this->translator->trans('Add Products to Categories', [], 'Modules.Pannizbulkproductcategory.Admin'),
            'form' => $form->createView(),
            'productCount' => count($productIds),
            'cancel_url' => $cancelUrl,
        ]);
    }

    private function assignProductsToCategories(array $productIds, array $categoryIds): void
    {
        if (empty($productIds) || empty($categoryIds)) {
            return;
        }

        foreach ($productIds as $productId) {
            $product = new \Product($productId);
            $product->addToCategories($categoryIds);
        }
    }
}
