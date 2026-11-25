<?php

namespace App\Purify;

use HTMLPurifier_HTMLDefinition;
use Stevebauman\Purify\Definitions\Definition;
use Stevebauman\Purify\Definitions\Html5Definition;

/** Html5Definition plus data-icon, the marker of an icon inside the text of a card (see Card::withIcons). */
class CardDefinition implements Definition
{
    public static function apply(HTMLPurifier_HTMLDefinition $definition)
    {
        Html5Definition::apply($definition);

        $definition->addAttribute('span', 'data-icon', 'Text');
        $definition->addAttribute('span', 'data-text-color', 'Text');
        $definition->addAttribute('blockquote', 'data-callout-icon', 'Text');
        $definition->addAttribute('blockquote', 'data-callout-title', 'Text');
        $definition->addAttribute('blockquote', 'data-callout-color', 'Text');
        $definition->addAttribute('blockquote', 'data-callout-icon-color', 'Text');
    }
}
