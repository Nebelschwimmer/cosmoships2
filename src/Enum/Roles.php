<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Roles: string implements TranslatableInterface
{
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_DEVELOPER = 'ROLE_DEVELOPER';
    case ROLE_MANAGER = 'ROLE_MANAGER';
    case ROLE_USER = 'ROLE_USER';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return match ($this) {
            self::ROLE_ADMIN => $translator->trans('admin', locale: $locale, domain: 'roles'),
            self::ROLE_DEVELOPER => $translator->trans('developer', locale: $locale, domain: 'roles'),
            self::ROLE_MANAGER => $translator->trans('manager', locale: $locale, domain: 'roles'),
            self::ROLE_USER => $translator->trans('user', locale: $locale, domain: 'roles'),
        };
    }

    public static function list(?TranslatorInterface $translator = null, ?string $locale = null): array
    {
        return array_map(function (Roles $case) use ($translator, $locale) {
            return [
                'name' => $translator ? $case->trans($translator, $locale) : $case->name,
                'value' => $case->value,
            ];
        }, self::cases());
    }
}
