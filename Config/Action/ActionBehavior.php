<?php

namespace Padam87\AdminBundle\Config\Action;

/**
 * How an action is requested. Entity, global and table actions request their url,
 * batch actions submit the selected entities (the batch form) to it.
 */
enum ActionBehavior: string
{
    /**
     * The page goes to the url (GET), eg. another list.
     */
    case Navigate = 'navigate';

    /**
     * The url opens in a new tab (GET), eg. a printable page.
     */
    case NewTab = 'new_tab';

    /**
     * The url is requested in an action modal (GET): a form opens in the modal, a file is saved, data updates the list.
     * Without a modal (eg. a twig rendered list) it is a link.
     */
    case Modal = 'modal';

    /**
     * Submitted (POST, or the method of the action, eg. DELETE), the response updates the list like Modal.
     */
    case Submit = 'submit';
}
