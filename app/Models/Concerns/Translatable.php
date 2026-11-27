<?php

namespace App\Models\Concerns;

/**
 * The text fields of a model are written in Spanish; their translations live in the columns <field>_en and <field>_ja.
 */
trait Translatable
{
    /**
     * The field in the language of the visitor, or in Spanish when that translation is empty.
     */
    public function tr(string $field): mixed
    {
        $locale = app()->getLocale();

        return $locale !== 'es' && filled($this->getAttribute("{$field}_{$locale}"))
            ? $this->getAttribute("{$field}_{$locale}")
            : $this->getAttribute($field);
    }
}
