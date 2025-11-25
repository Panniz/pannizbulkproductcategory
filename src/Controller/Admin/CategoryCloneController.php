<?php

namespace Panniz\BulkProductCategory\Controller\Admin;

use Doctrine\DBAL\Connection;
use Panniz\BulkProductCategory\Form\Type\CategoryCloneType;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Category;
use Validate;

class CategoryCloneController extends FrameworkBundleAdminController
{
    private Connection $connection;
    private TranslatorInterface $translator;
    private LegacyContext $legacyContext;

    public function __construct(
        Connection $connection,
        TranslatorInterface $translator,
        LegacyContext $legacyContext
    ) {
        $this->connection = $connection;
        $this->translator = $translator;
        $this->legacyContext = $legacyContext;
    }

    public function index(Request $request): Response
    {
        // Bulk actions usually send data via POST in 'category_category_bulk' or similar, 
        // but user confirmed 'category_id_category'.
        // We check both query (GET) and request (POST) bags.
        $categoryIds = $request->request->get('category_id_category', []);
        if (empty($categoryIds)) {
            $categoryIds = $request->query->get('category_id_category', []);
        }

        $categoryIdsAsString = is_array($categoryIds) ? implode(',', $categoryIds) : $categoryIds;

        $form = $this->createForm(CategoryCloneType::class, ['selected_categories' => $categoryIdsAsString]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $selectedCategoryIds = array_filter(explode(',', $data['selected_categories']));
            $destinationCategoryId = $data['destination_category'];
            $recursive = $data['recursive'] ?? false;

            if (!empty($selectedCategoryIds) && $destinationCategoryId) {
                $count = 0;
                foreach ($selectedCategoryIds as $sourceId) {
                    $this->cloneCategory((int)$sourceId, (int)$destinationCategoryId, (bool)$recursive);
                    $count++;
                }

                $this->addFlash('success', $this->translator->trans(
                    'Clonate con successo %count% categorie.',
                    ['%count%' => $count],
                    'Modules.Pannizbulkproductcategory.Admin'
                ));

                return $this->redirectToRoute('admin_categories_index');
            }
        }

        $cancelUrl = $this->legacyContext->getContext()->link->getAdminLink('AdminCategories');

        return $this->render('@Modules/pannizbulkproductcategory/views/templates/admin/clone_form.html.twig', [
            'layoutTitle' => $this->translator->trans('Clona Categorie', [], 'Modules.Pannizbulkproductcategory.Admin'),
            'form' => $form->createView(),
            'categoryCount' => count(array_filter(explode(',', $categoryIdsAsString))),
            'cancel_url' => $cancelUrl,
        ]);
    }

    private function cloneCategory(int $sourceId, int $destinationParentId, bool $recursive = false): void
    {
        $sourceCategory = new Category($sourceId);
        if (!Validate::isLoadedObject($sourceCategory)) {
            return;
        }

        // Create new category
        $newCategory = new Category();
        $newCategory->id_parent = $destinationParentId;
        $newCategory->name = $sourceCategory->name;
        $newCategory->link_rewrite = $sourceCategory->link_rewrite;
        $newCategory->description = $sourceCategory->description;
        $newCategory->active = $sourceCategory->active;
        $newCategory->id_shop_default = $sourceCategory->id_shop_default;

        // Copy other properties as needed

        if ($newCategory->add()) {
            if ($recursive) {
                // Clone children
                $children = Category::getChildren($sourceId, $this->legacyContext->getContext()->language->id);
                foreach ($children as $child) {
                    $this->cloneCategory((int)$child['id_category'], (int)$newCategory->id, true);
                }
            }
        }
    }
}
