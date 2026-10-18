<?php

namespace Padam87\AdminBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * admin_list(config, table, pagination, filters): data of a serialized list (see Padam87\AdminBundle\Listing\ListBuilder).
 */
class ListExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_list', [ListRuntime::class, 'build']),
        ];
    }
}
