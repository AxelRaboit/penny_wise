<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\BudgetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomepageController extends AbstractController
{
    private const string HOMEPAGE_TEMPLATE = 'homepage/index.html.twig';

    public function __construct(private readonly BudgetRepository $budgetRepository) {}

    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {
        $options = [
            'budgets' => $this->budgetRepository->findAllBudgets(),
        ];

        return $this->render(self::HOMEPAGE_TEMPLATE, $options);
    }
}
