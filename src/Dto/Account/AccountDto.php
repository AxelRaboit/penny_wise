<?php

declare(strict_types=1);

namespace App\Dto\Account;

use App\Dto\Wallet\YearDto;

final readonly class AccountDto
{
    /**
     * @param YearDto[] $years
     */
    public function __construct(
        private int $id,
        private string $name,
        private array $years
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return YearDto[]
     */
    public function getYears(): array
    {
        return $this->years;
    }

    /**
     * Create an AccountDto from an array of data.
     *
     * @param array{id: int, name: string, years: YearDto[]} $data the data to create the AccountDto from
     *
     * @return AccountDto the newly created AccountDto instance
     */
    public static function createFrom(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            years: $data['years']
        );
    }
}
