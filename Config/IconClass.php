<?php

namespace Padam87\AdminBundle\Config;

/**
 * The class of an icon by its name, with the icon_class format of the bundle configuration
 * (eg. `bi bi-%s` for Bootstrap Icons, `ti ti-%s` for Tabler Icons).
 */
class IconClass
{
    public function __construct(private string $format)
    {
    }

    public function __invoke(string $name): string
    {
        return sprintf($this->format, $name);
    }
}
