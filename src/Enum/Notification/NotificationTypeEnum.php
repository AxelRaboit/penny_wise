<?php

declare(strict_types=1);

namespace App\Enum\Notification;

enum NotificationTypeEnum: string
{
    case FriendshipRequest = 'friendship_request';

    private function label(): string
    {
        return match ($this) {
            self::FriendshipRequest => 'Friendship Request',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getKey(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return array<string, string>
     */
    public function getDetails(): array
    {
        return [
            'key' => $this->getKey(),
            'label' => $this->label(),
            'value' => $this->getValue(),
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function getAllDetails(): array
    {
        return array_map(fn($type) => $type->getDetails(), self::cases());
    }
}
