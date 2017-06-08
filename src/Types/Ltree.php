<?php
namespace App\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;


class Ltree extends Type
{
    const NAME = 'ltree';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return self::NAME;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function getName()
    {
        return self::NAME;
    }
}