<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Expense;
use App\Form\ExpenseFormType;
use App\Repository\ExpenseRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Serializer\SerializerInterface;

class ExpenseController extends AbstractController
{
    #[Route('/expense', name: 'app_expense')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $expenses = $entityManager->getRepository(Expense::class)->findAll();
        return $this->render('expense/index.html.twig', [
            'expenses' => $expenses,
        ]);
    }

    #[Route('/expense_insert', name: 'expense_form')]
    public function insert(Request $request, EntityManagerInterface $entityManager): Response
    {
        $expense = new Expense();
        $form = $this->createForm(ExpenseFormType::class, $expense);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $expense = $form->getData();

            $entityManager->persist($expense);
            $entityManager->flush();

            return $this->redirectToRoute('app_expense');
        }

        return $this->render('expense/edit.html.twig', [
            'title' => 'Insert expense',
            'expenseForm' => $form,
        ]);
    }

    #[Route('/expense_edit/{id}', name: 'expense_form_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $expense = $entityManager->getRepository(Expense::class)->find($id);

        $form = $this->createForm(ExpenseFormType::class, $expense);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $expense->setCategory($data->getCategory());
            $expense->setPrice($data->getPrice());
            $expense->setQuantity($data->getQuantity());
            $expense->setDate($data->getDate());

            $entityManager->persist($expense);
            $entityManager->flush();

            return $this->redirectToRoute('app_expense');
        }

        return $this->render('expense/edit.html.twig', [
            'title' => 'Edit expense',
            'id' => $id,
            'expenseForm' => $form,
        ]);
    }

    /**
     * Create a new expense.
     */
    #[Route('/api/expense', methods: ['POST'])]
    #[OA\Response(
        response: 201,
        description: 'Created expense',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Expense::class, groups: ['full']))
        )
    )]
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $expense = new Expense();

        $categoryID = $request->request->get('categoryID');
        $price = $request->request->get('price');
        $quantity = $request->request->get('quantity');
        $date = $request->request->get('date');

        $category = $entityManager->getRepository(Category::class)->find($categoryID);

        $expense->setCategory($category);
        $expense->setPrice($price);
        $expense->setQuantity($quantity);
        $expense->setDate(new DateTime($date));

        $entityManager->persist($expense);
        $entityManager->flush();

        return $this->json($expense, 201);
    }

    /**
     * Fetch a single expense.
     */
    #[Route('/api/expense/{id}', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Fetched expense',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Expense::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Id of the expense',
        schema: new OA\Schema(type: 'int')
    )]
    public function get(int $id, ExpenseRepository $expenseRepository, SerializerInterface $serializer): Response
    {
        $expense = $expenseRepository->find($id);
        return $this->json($expense);
    }

    /**
     * Updates an existing expense.
     */
    #[Route('/api/expense/{id}', methods: ['PUT'])]
    #[OA\Response(
        response: 200,
        description: 'Updated expense',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Expense::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Id of the expense',
        schema: new OA\Schema(type: 'int')
    )]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager,
                           SerializerInterface $serializer): Response
    {
        $categoryID = $request->request->get('categoryID');
        $price = $request->request->get('price');
        $quantity = $request->request->get('quantity');

        $expense = $entityManager->getRepository(Expense::class)->find($id);

        if (!$expense) {
            throw $this->createNotFoundException(
                'No expense found for id ' . $id
            );
        }

        $category = $entityManager->getRepository(Category::class)->find($categoryID);

        $expense->setCategory($category);
        $expense->setPrice($price);
        $expense->setQuantity($quantity);

        $entityManager->persist($expense);
        $entityManager->flush();

        return $this->json($expense);
    }

    /**
     * Deletes a expense.
     */
    #[Route('/api/expense/{id}', methods: ['DELETE'])]
    #[OA\Response(
        response: 204,
        description: 'The expense was deleted successfully',
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Id of the expense',
        schema: new OA\Schema(type: 'int')
    )]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $expense = $entityManager->getRepository(Expense::class)->find($id);

        if (!$expense) {
            throw $this->createNotFoundException(
                'No expense found for id ' . $id
            );
        }

        $entityManager->remove($expense);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    /**
     * Queries expenses.
     */
    #[Route('/api/expense', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Queried expenses',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Expense::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'categoryID',
        in: 'query',
        description: 'Category ID of the expense',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Parameter(
        name: 'priceMin',
        in: 'query',
        description: 'Minimum price of the expense',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'priceMax',
        in: 'query',
        description: 'Maximum price of the expense',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'date',
        in: 'query',
        description: 'Date when expense was made',
        schema: new OA\Schema(type: 'string')
    )]
    public function search(Request $request, ExpenseRepository $expenseRepository,
                           SerializerInterface $serializer): Response
    {
        $categoryID = $request->query->get('categoryID');
        $priceMin = $request->query->get('priceMin');
        $priceMax = $request->query->get('priceMax');
        $date = $request->query->get('date');

        $expense = $expenseRepository->search($categoryID, $priceMin, $priceMax, $date);

        return $this->json($expense);
    }

    #[Route('/api/expense/aggregateByDate', methods: ['GET'])]
    public function aggregateByDate(ExpenseRepository $expenseRepository,
                           SerializerInterface $serializer): Response
    {
        $result = $expenseRepository->aggregateByDate();

        return $this->json($result);
    }
}
