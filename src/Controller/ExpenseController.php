<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Form\ExpenseFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ExpenseController extends AbstractController
{
    public function index(EntityManagerInterface $entityManager): Response
    {
        $expenses = $entityManager->getRepository(Expense::class)->findAll();
        return $this->render('expense/index.html.twig', [
            'expenses' => $expenses,
        ]);
    }

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

}
