<?php
namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum OrderOptions: string implements TranslatableInterface
{
  case ASC = 'asc';
  case DESC = 'desc';


  public function trans(TranslatorInterface $translator, ?string $locale = null): string
  {
    return $translator->trans($this->value, [], 'order_options', $locale);
  }
  public static function list(?TranslatorInterface $translator = null, ?string $locale = null): array
  {
    return array_map(
      function (self $option) use ($translator, $locale) {
        return ['name' => $option->trans($translator, $locale), 'value' => $option->value];
      },
      self::cases(),
    );
  }

}