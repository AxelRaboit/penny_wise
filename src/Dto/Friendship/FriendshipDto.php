<?php

declare(strict_types=1);

namespace App\Dto\Friendship;

use App\Entity\User;

final readonly class FriendshipDto
{
    public function __construct(
        private int $id,
        private User $friend,
        private bool $isAccepted,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getFriend(): User
    {
        return $this->friend;
    }

    public function isAccepted(): bool
    {
        return $this->isAccepted;
    }

    /**
     * Create a FriendshipDto from an array of data.
     *
     * @param array{id: int, friend: User, isAccepted: bool} $data
     */
    public static function createFrom(array $data): self
    {
        return new self(
            id: $data['id'],
            friend: $data['friend'],
            isAccepted: $data['isAccepted'],
        );
    }
}
