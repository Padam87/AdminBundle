<?php

namespace Padam87\AdminBundle\Config\Action;

use Twig\Template;

class SubmittedAction extends Action
{
    private string $method = 'POST';

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getTemplate(): Template|string
    {
        return '@Padam87Admin/action/submitted_action.html.twig';
    }
}
