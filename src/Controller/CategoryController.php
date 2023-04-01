<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Expense;
use App\Form\CategoryFormType;
use App\Form\ExpenseFormType;
use App\Repository\CategoryRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Category::class)->findAll();
        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category_edit', name: 'category_form')]
    public function insert(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryFormType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_category');
        }

        return $this->render('category/edit.html.twig', [
            'title'        => 'Insert category',
            'categoryForm' => $form,
        ]);
    }


    #[Route('/category_edit/{id}', name: 'category_form_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = $entityManager->getRepository(Category::class)->find($id);

        $form = $this->createForm(CategoryFormType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $category->setName($data->getName());

            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_category');
        }

        return $this->render('category/edit.html.twig', [
            'title' => 'Edit category',
            'id' => $id,
            'categoryForm' => $form,
        ]);
    }

    /**
     * Create a new category.
     */
    #[Route('/api/category', methods: ['POST'])]
    #[OA\Response(
        response: 201,
        description: 'Created category',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Category::class, groups: ['full']))
        )
    )]
    public function create(EntityManagerInterface $entityManager,
                           SerializerInterface $serializer): Response
    {
        $category = new Category();
        $category->setName('Keyboard');
        $category->setLastUpdated(new DateTime());

        $entityManager->persist($category);
        $entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('query')
            ->toArray();
        $json = $serializer->serialize($category, 'json', $context);

        return new JsonResponse($json, 201);
    }

    /**
     * Fetch a single category.
     */
    #[Route('/api/category/{id}', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Fetched category',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Category::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Id of the category',
        schema: new OA\Schema(type: 'int')
    )]
    public function get(int $id, CategoryRepository $categoryRepository,
                        SerializerInterface $serializer): Response
    {
        $category = $categoryRepository->find($id);

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('query')
            ->toArray();
        $json = $serializer->serialize($category, 'json', $context);

        return new JsonResponse($json);
    }

    /**
     * Updates an existing category.
     */
    #[Route('/api/category/{id}', methods: ['PUT'])]
    #[OA\Response(
        response: 200,
        description: 'Updated category',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Category::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Id of the category',
        schema: new OA\Schema(type: 'int')
    )]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager,
                           SerializerInterface $serializer): Response
    {
        $id = $request->request->get('id');
        $name = $request->request->get('name');

        $category = $entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            throw $this->createNotFoundException(
                'No category found for id '.$id
            );
        }


        $category->setName($name);
        $entityManager->persist($category);
        $entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('query')
            ->toArray();
        $json = $serializer->serialize($category, 'json', $context);

        return new JsonResponse($json);
    }

    /**
     * Deletes a category.
     */
    #[Route('/api/category/{id}', methods: ['DELETE'])]
    #[OA\Response(
        response: 204,
        description: 'The category was deleted successfully',
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Id of the category',
        schema: new OA\Schema(type: 'int')
    )]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $category = $entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            throw $this->createNotFoundException(
                'No category found for id '.$id
            );
        }

        $entityManager->remove($category);
        $entityManager->flush();

        return new JsonResponse(null, 204);
    }
}
