<?php
// in src/Controller/Admin/BulkAssignCategoryController.php
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
    private Connection $connection;
    private TranslatorInterface $translator;
    private LegacyContext $legacyContext;

    public function __construct(
        Connection $connection,
        TranslatorInterface $translator,
        LegacyContext $legacyContext
    ) {
        // Il costruttore che hai fornito è corretto, ma PHP 8.1+ permette una sintassi più corta
        $this->connection = $connection;
        $this->translator = $translator;
        $this->legacyContext = $legacyContext;
    }

    public function index(Request $request): Response
    {
        $productIds = $request->get('product_ids', []);
        $productIdsAsString = implode(',', $productIds);

        $form = $this->createForm(BulkCategoryAssignType::class, ['product_ids' => $productIdsAsString]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $submittedProductIds = array_filter(explode(',', $data['product_ids']));
            $categoryIds = $data['categories'];
            $action = $data['action'];

            if ($action === 'add') {
                $this->assignProductsToCategories($submittedProductIds, $categoryIds);
                $message = 'Categorie aggiunte con successo per %count% prodotti.';
            } elseif ($action === 'remove') {
                $this->removeProductsFromCategories($submittedProductIds, $categoryIds);
                $message = 'Categorie rimosse con successo per %count% prodotti.';
            }

            $this->addFlash('success', $this->translator->trans(
                $message,
                ['%count%' => count($submittedProductIds)],
                'Admin.Notifications.Success'
            ));

            // CORREZIONE: Il nome corretto della rotta è 'admin_products_index'
            return $this->redirectToRoute('admin_products_index');
        }

        $cancelUrl = $this->legacyContext->getContext()->link->getAdminLink('AdminProducts');

        return $this->render('@Modules/pannizbulkproductcategory/views/templates/admin/bulk_assign_form.html.twig', [
            'layoutTitle' => $this->translator->trans('Modifica Categorie Prodotti', [], 'Modules.Pannizbulkproductcategory.Admin'),
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
            $product = new \Product((int)$productId);
            $product->addToCategories($categoryIds);
        }
    }

    private function removeProductsFromCategories(array $productIds, array $categoryIds): void
    {
        if (empty($productIds) || empty($categoryIds)) {
            return;
        }

        foreach ($productIds as $productId) {
            $product = new \Product((int)$productId);
            foreach ($categoryIds as $categoryId) {
                $product->deleteCategory($categoryId);
            }
        }
    }
}