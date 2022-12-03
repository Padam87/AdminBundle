<?php

namespace Padam87\AdminBundle\Translation;

use Padam87\AdminBundle\Controller\AdminController;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\TranslatableMessage;

class AdminExtractor implements ExtractorInterface
{
    private string $prefix;
    private bool $ran = false;

    private iterable $admins;
    private FormFactoryInterface $factory;
    private LoggerInterface $logger;

    public function __construct(iterable $admins, FormFactoryInterface $factory, LoggerInterface $logger)
    {
        $this->admins = $admins;
        $this->factory = $factory;
        $this->logger = $logger;
    }

    public function extract($resource, MessageCatalogue $catalogue)
    {
        if (!$this->ran) {
            /** @var AdminController $controller */
            foreach ($this->admins as $controller) {
                $this->extractConfig($controller, $catalogue);
                $this->extractList($controller, $catalogue);
                $this->extractForm($controller, $catalogue);
            }

            $this->ran = true;
        }
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    private function addMessage(TranslatableMessage|string|null $message, MessageCatalogue $catalogue)
    {
        if ($message === null) {
            return;
        }

        if ($message instanceof TranslatableMessage) {
            $catalogue->set($message->getMessage(), '__' . $message->getMessage(), $message->getDomain() ?? 'messages');
        } else {
            $catalogue->set($message, '__' . $message);
        }
    }

    protected function extractConfig(AdminController $controller, MessageCatalogue $catalogue)
    {
        $config = $controller->getConfig();

        foreach ([$config->getSingularName(), $config->getPluralName()] as $message) {
            $this->addMessage($message, $catalogue);
        }

        foreach ($config->getActions() as $action) {
            $this->addMessage($action->getTitle(), $catalogue);
        }
    }

    protected function extractList(AdminController $controller, MessageCatalogue $catalogue)
    {
        $table = $controller->createTable();

        foreach ($table->getColumns() as $column) {
            $title = $column->getTitle();

            $catalogue->set($title, '__' . $title);
        }

        foreach ($table->getFilterSets() as $filterSet) {
            if (null === $name = $filterSet->getName()) {
                continue;
            }

            $catalogue->set($name, '__' . $name);
        }
    }

    protected function extractForm(AdminController $controller, MessageCatalogue $catalogue)
    {
        $fqcn = $controller->getConfig()->getEntityFqcn();

        $entity = new $fqcn();

        if ($controller->getFormFqcn($entity) === null) {
            return;
        }

        try {
            $type = $controller->getFormFqcn($entity);
            $options = $controller->getFormOptions($entity);
            $options['csrf_protection'] = false;

            $form = $this->factory->create($type, $entity, $options);

            $labels = $this->extractView($form->createView());
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf('[%s] [%s] %s', get_class($controller), $type, $e->getMessage()));

            return;
        }

        foreach ($labels as $label) {
            if ($label['id'] === null) {
                continue;
            }

            $catalogue->add([$label['id'] => $label['translation']], $label['domain'] ?? 'messages');
        }
    }

    protected function extractView(FormView $view, ?string $domain = null): array
    {
        $labels = [];

        if ($domain === null) {
            $domain = $view->vars['translation_domain'];
        }

        foreach ($view as $field) {
            if (array_key_exists('help', $field->vars) && null !== $field->vars['help']) {
                $labels[] = ['id' => $field->vars['help'], 'translation' => '__' . $field->vars['help'], 'domain' => $domain];
            }

            if (array_key_exists('attr', $field->vars) && array_key_exists('placeholder', $field->vars['attr'])) {
                $labels[] = ['id' => $field->vars['attr']['placeholder'], 'translation' => $field->vars['attr']['placeholder'], 'domain' => $domain];
            }

            if (count($field) > 0) {
                if (array_key_exists('choices', $field->vars)) {
                    if ($field->vars['choice_translation_domain'] !== false) {
                        $labels = array_merge($labels, $this->extractView($field, $field->vars['choice_translation_domain']));
                    }
                } else {
                    $labels = array_merge($labels, $this->extractView($field, $domain));
                }
            }

            if ($field->vars['name'] === '_token') {
                continue;
            }

            if (false === $id = $field->vars['label']) {
                continue;
            }

            if (empty($id)) {
                continue;
            }

            $labels[] = ['id' => $id, 'translation' => '__' . $id, 'domain' => $domain];
        }

        return $labels;
    }
}
