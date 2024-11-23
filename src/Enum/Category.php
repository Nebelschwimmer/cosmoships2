<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Category: string implements TranslatableInterface
{
    case CIVILIAN = 'civilian_ships';
    case MILITARY = 'combat_ships';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return match ($this) {
            self::CIVILIAN => $translator->trans('civilian', locale: $locale, domain: 'category_options'),
            self::MILITARY => $translator->trans('military', locale: $locale, domain: 'category_options'),
        };
    }

    public static function list(?TranslatorInterface $translator = null, ?string $locale = null): array
    {
        return array_map(function (Category $case) use ($translator, $locale) {
            return [
                'name' => $translator ? $case->trans($translator, $locale) : $case->name,
                'value' => $case->value,
            ];
        }, self::cases());
    }
}
