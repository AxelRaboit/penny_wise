<?php

declare(strict_types=1);

namespace App\Twig;

use App\Enum\Transaction\TransactionCategoryEnum;
use App\Enum\Wallet\MonthEnum;
use InvalidArgumentException;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use ValueError;

use function Symfony\Component\String\u;

class AppExtension extends AbstractExtension
{
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('month_name', $this->getMonthName(...)),
            new TwigFilter('month_abbreviation', $this->getMonthAbbreviation(...)),
            new TwigFilter('is_transaction_category', $this->isTransactionCategory(...)),
        ];
    }

    /**
     * Returns the name of the month using MonthEnum.
     *
     * @param int $monthNumber The number of the month (1 for January, 12 for December)
     *
     * @return string The name of the month, or an error message if the month number is invalid
     */
    public function getMonthName(int $monthNumber): string
    {
        try {
            $monthEnum = MonthEnum::from($monthNumber);

            return $monthEnum->getName();
        } catch (ValueError $valueError) {
            return sprintf('Invalid month number: %s', $valueError->getMessage());
        }
    }

    /**
     * Returns the first three letters of the month.
     *
     * @param int $monthNumber The number of the month (1 for January, 12 for December)
     *
     * @return string The abbreviated name of the month, or an error message if the month number is invalid
     */
    public function getMonthAbbreviation(int $monthNumber): string
    {
        try {
            $monthEnum = MonthEnum::from($monthNumber);

            return u($monthEnum->getName())->slice(0, 3)->toString();
        } catch (ValueError $valueError) {
            return sprintf('Invalid month number: %s', $valueError->getMessage());
        } catch (InvalidArgumentException $invalidArgumentException) {
            return sprintf('Error processing the month name: %s', $invalidArgumentException->getMessage());
        }
    }

    /**
     * Checks if the given category matches the provided TransactionCategoryEnum.
     *
     * @param string                  $category The transaction category
     * @param TransactionCategoryEnum $enum     The enum to compare against
     *
     * @return bool True if they match, false otherwise
     */
    public function isTransactionCategory(string $category, TransactionCategoryEnum $enum): bool
    {
        return TransactionCategoryEnum::tryFrom($category)?->value === $enum->value;
    }
}
