<?php

namespace Padam87\AdminBundle\Config\Action;

/**
 * How prominent an action is: the theme color of its button (Bootstrap), or a link.
 */
enum ActionVariant: string
{
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Success = 'success';
    case Danger = 'danger';
    case Warning = 'warning';
    case Info = 'info';
    case Light = 'light';
    case Dark = 'dark';
    case Link = 'link';
}
