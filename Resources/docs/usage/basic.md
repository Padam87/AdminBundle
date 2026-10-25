# Usage

## Basic

```php
<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use Padam87\AdminBundle\Config\AdminConfig;
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
            ->setIcon('box') // adds an icon (its name, see icon_class), usable in the theme for this module
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
                ->setBehavior(ActionBehavior::Submit)
                ->setVariant(ActionVariant::Success)
                ->setIcon('cart-plus')
                ->setConfirmation('Add it to the cart?')
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

#### Behaviors and variants

`ActionBehavior` is how the action is requested (batch actions submit the selected entities, the batch form):

 - `Navigate` the page goes to the url (default)
 - `NewTab` the url opens in a new tab
 - `Modal` the url is requested in an action modal (a JS rendered list): a form opens in the modal, a file is saved,
   data updates the list. Rendered by twig it is a link with `data-action-behavior="modal"`
 - `Submit` submitted, POST or the method of the action (`setMethod('DELETE')`)

`ActionVariant` is the theme color of its button (`Primary`, `Secondary`, ... `Link`).

Icons are names, the class is built by the `icon_class` option (Bootstrap Icons by default):

```yaml
padam87_admin:
    icon_class: 'ti ti-%s' # Tabler Icons
    actions:
        edit: { icon: pencil }
```

The default actions are configured the same way (`behavior`, `variant`, `method`, `icon`, `confirmation`):

```yaml
padam87_admin:
    actions:
        create: { behavior: modal }
        delete: { confirmation: 'Are you sure?' }
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
