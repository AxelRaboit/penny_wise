<?php

namespace App\DBAL\Types;

use App\Enum\TransactionTypeEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TransactionTypeType extends Type
{
    private const TRANSACTIONTYPE = 'transactiontype';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $values = implode("', '", TransactionTypeEnum::getAllowedValues());
        return "ENUM('$values')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?TransactionTypeEnum
    {
        return !empty($value) ? new TransactionTypeEnum($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value?->getValue();
    }

    public function getName(): string
    {
        return self::TRANSACTIONTYPE;
    }
}