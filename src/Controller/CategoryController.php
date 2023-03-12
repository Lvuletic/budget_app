<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(): Response
    {
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    public function create(EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $category->setName('Keyboard');
        $category->setLastUpdated(new DateTime());

        $entityManager->persist($category);
        $entityManager->flush();

        return new Response('Saved new category with id '.$category->getCategoryID());
    }

    public function get(int $id, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);
        return new Response($category);
    }

    public function update(EntityManagerInterface $entityManager, int $id): Response
    {
        $category = $entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            throw $this->createNotFoundException(
                'No category found for id '.$id
            );
        }

        $category->setName('New product name!');
        $entityManager->flush();

        return $this->redirectToRoute('product_show', [
            'id' => $category->getId()
        ]);
    }

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
    }
}
