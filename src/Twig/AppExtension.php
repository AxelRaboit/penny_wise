<?php

namespace App\Twig;

use Override;
use App\Enum\MonthEnum;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use ValueError;

class AppExtension extends AbstractExtension
{
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('month_name', $this->getMonthName(...)),
        ];
    }

    public function getMonthName(int $monthNumber): string
    {
        try {
            $monthEnum = MonthEnum::from($monthNumber);
            return $monthEnum->getName();
        } catch (ValueError $valueError) {
            return "Invalid month number: " . $valueError->getMessage();
        }
    }
}
