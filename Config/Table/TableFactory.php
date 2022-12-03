<?php

namespace Padam87\AdminBundle\Config\Table;

use Padam87\AdminBundle\Config\AdminConfig;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class TableFactory
{
    public function __construct(
        private array $config,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function create(AdminConfig $config): Table
    {
        $refl = new \ReflectionClass($config->getEntityFqcn());

        $table = new Table();

        $table->setLabelFormat((new CamelCaseToSnakeCaseNameConverter())->normalize($refl->getShortName()) . '.%name%');
        $table->setQueryAlias(strtolower(preg_replace('/[a-z]/', '', $refl->getShortName())));
        $table->setFilters($this->createFilters());

        return $table;
    }

    protected function createFilters(): ?FormBuilderInterface
    {
        return $this->formFactory->createNamedBuilder(
            false,
            FormType::class,
            null,
            [
                'method' => 'GET',
                'required' => false,
                'csrf_protection' => false,
                'allow_extra_fields' => true,
            ]
        );
    }
}
