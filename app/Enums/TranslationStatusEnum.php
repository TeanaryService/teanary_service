<?php

namespace App\Enums;

enum TranslationStatusEnum: string
{
    case NotTranslated = 'not_translated';  // 不翻译
    case Pending = 'pending';                // 待翻译
    case Translated = 'translated';          // 已翻译

    public function label(): string
    {
        return match ($this) {
            self::NotTranslated => '不翻译',
            self::Pending => '待翻译',
            self::Translated => '已翻译',
        };
    }

    public static function default(): self
    {
        return self::NotTranslated;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
