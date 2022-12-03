# Usage

## Basic

```php
<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use Padam87\AdminBundle\Config\AdminConfig;
use Padam87\AdminBundle\Config\HtmlElement;
use Padam87\AdminBundle\Config\Table\Column\Column;
use Padam87\AdminBundle\Config\Table\Table;
use Padam87\AdminBundle\Controller\AdminController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/products', name: 'admin_product_')]
class ProductController extends AdminController
{
    protected function getEntityFqcn()
    {
        return Product::class;
    }

    public function getFormFqcn($entity): ?string
    {
        return ProductType::class;
    }

    protected function configure(AdminConfig $config): void
    {
        $config
            ->setIcon(new HtmlElement('i', ['class' => 'bi bi-box'])) // adds an icon, usable in the theme for this module
            ->setSingularName('product') // singular and plural names, inferred from the entity classname by default
            ->setPluralName('products')
        ;
    }

    protected function table(Table $table): void
    {
        $table
            ->addColumn(Column::create('id'))
            ->addColumn(Column::create('name'))
        ;
    }
}
```

- This basic configuration will provide you with CRUD functions and batch DELETE.
- The form in use a regular, same old Symfony form.
- The table configuration is completely unrelated to the form.

## Configuring actions

### Removing an action

```php
protected function configure(AdminConfig $config): void
{
    $config
        ->removeAction(Action::BATCH_DELETE)
    ;
}
```

### Creating an action

```php
protected function configure(AdminConfig $config): void
{
    $config
        ->addAction(
            Action::create('addToCart', Action::TYPE_ENTITY)
                ->setRouteName('admin_product_addToCart')
                ->setRouteParameters(fn($entity) => ['id' => $entity->getId()])
                ->setControl(new HtmlElement('a', ['class' => 'btn btn-success']))
                ->setIcon(new HtmlElement('i', ['class' => 'bi bi-cart-plus']))
                ->setTitle('Add to cart')
                ->setCondition(function (Product $product) {
                    return $product->isAvailable();
                })
        )
    ;
}
```

```php
#[Route('/{id}/add-to-cart', name: 'addToCart')]
public function addToCart(Product $product)
{
    // just a normal action, like you would to without this bundle
}
```

#### Action types

 - `TYPE_GLOBAL` Actions related to the module directly, eg create
 - `TYPE_ENTITY` Actions related to en entity, eg edit
 - `TYPE_BATCH` Actions related to a selection of entities, eg batch delete
 - `TYPE_TABLE` Actions related to current table, with filters, eg export (not included in the bundle)

## Configuring the table

### Adding columns

```php
protected function table(Table $table): void
{
    $table
        ->addColumn(Column::create('id'))
        ->addColumn(Column::create('name'))
    ;
}
```

#### Column types

```php
protected function table(Table $table): void
{
    $table
        ->addColumn(DateTimeColumn::create('createdAt'))
    ;
}
```

Built in column types can be found [here](../../../Config/Table/Column)

### Filters

```php
protected function table(Table $table): void
{
    $table->getFilters()
        ->add('name', TextType::class, ['filter_expr' => 'like'])
    ;

    $table
        ->addColumn(Column::create('name')->setFilters(['name']))
    ;
}
```

- `$table->getFilters()` returns a regular `FormBuilderInterface`, you can use it the same way as you would build any Symfony form.
- For information about the filter form, and how it translates to queries please read the documentation of [FormFilterBundle](https://github.com/Padam87/FormFilterBundle)
