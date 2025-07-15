<?php

namespace App\Enums;

enum AttributeTypeEnum: string
{
    case Select = 'select';
    case Multiselect = 'multiselect';
    case Text = 'text';

    public function label(): string
    {
        return match ($this) {
            self::Select => '单选',
            self::Multiselect => '多选',
            self::Text => '文本',
        };
    }

    public static function default(): self
    {
        return self::Select;
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
