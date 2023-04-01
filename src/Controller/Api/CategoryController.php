<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Serializer\SerializerInterface;

class CategoryController extends AbstractController
{

    /**
     * Create a new category.
     */
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

        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json($category, 201);
    }

    /**
     * Fetch a single category.
     */
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

        return $this->json($category);
    }

    /**
     * Updates an existing category.
     */
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

        return $this->json($category);
    }

    /**
     * Deletes a category.
     */
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

        return $this->json(null, 204);
    }
}
