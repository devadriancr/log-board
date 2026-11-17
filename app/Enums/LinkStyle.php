<?php

namespace App\Enums;

enum LinkStyle: string
{
    case Button = 'button';
    case Link = 'link';

    /**
     * Get the translated label of the style.
     */
    public function label(): string
    {
        return __('enums.link_style.'.$this->value);
    }
}
