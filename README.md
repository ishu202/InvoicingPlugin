# InvoicingPlugin

SyliusInvoicingPlugin creates new immutable invoice when the order is in given state (default: created) and allows
both customer and admin to download invoices related to the order.   

## Installation

Require plugin with composer:

```bash
composer require sylius/invoicing-plugin
```

Import routing:

```yaml
sylius_invoicing_plugin:
    resource: "@SyliusInvoicingPlugin/Resources/config/app/routing.yml"
```

Add plugin class to your `AppKernel`:

```php
$bundles = [
    new \Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
    new \Prooph\Bundle\ServiceBus\ProophServiceBusBundle(),
    new \Sylius\InvoicingPlugin\SyliusInvoicingPlugin(),
];
```

Configure `KnpSnappyBundle` with your path to `wkhtmltopdf`:

```yaml
knp_snappy:
    pdf:
        enabled: true
        binary: /usr/local/bin/wkhtmltopdf
        options: []
```

If you do not have this binary, you can download it [here](https://wkhtmltopdf.org/downloads.html).

Copy templates from

```
vendor/sylius/invoicing-plugin/src/Resources/views/SyliusAdminBundle/
```
to
```
app/Resources/SyliusAdminBundle/views/
```

Copy migrations from

```
vendor/sylius/invoicing-plugin/migrations/
```
to your migrations directory and run `bin/console doctrine:migrations:migrate`

Override Channel entity:

Write new class which will use ShopBillingDataTrait and implement ShopBillingDataAwareInterface:

```php
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\Channel as BaseChannel;
use Sylius\InvoicingPlugin\Entity\ShopBillingDataAwareInterface;
use Sylius\InvoicingPlugin\Entity\ShopBillingDataTrait;

/**
 * @MappedSuperclass
 * @Table(name="sylius_channel")
 */
class Channel extends BaseChannel implements ShopBillingDataAwareInterface
{
    use ShopBillingDataTrait;
}

```

And override the model's class in the `app/config/config.yml`:

```
sylius_channel:
    resources:
        channel:
            classes:
                model: AppBundle\Entity\Channel
```

Clear cache:

```bash
bin/console cache:clear
```

## Extension points

Majority of actions contained in SyliusInvoicingPlugin is executed once an event after changing the state of
the Order on `winzou_state_machine` is dispatched.

Here is the example:

```bash
$container->prependExtensionConfig('winzou_state_machine', [
    'sylius_order' => [
        'callbacks' => [
            'after' => [
                'sylius_invoicing_plugin_order_created_producer' => [
                    'on' => ['create'],
                    'do' => ['@Sylius\InvoicingPlugin\EventProducer\OrderPlacedProducer', '__invoke'],
                    'args' => ['object'],
                ],
            ],
        ],
    ],
]);
```

Code placed above is a part of logic placed in `SyliusInvoicingExtension` class.
You can customize this class by adding new state machine events listeners or editing existing ones.

Apart from that an Invoice model is treated as a Resource.

You can read more about Resources here:

<http://docs.sylius.com/en/1.2/components_and_bundles/bundles/SyliusResourceBundle/index.html>.

Hence, template for displaying the list of Invoices is defined in `routing.yml` file:

```
sylius_invoicing_plugin_invoice:
    resource: |
        alias: sylius_invoicing_plugin.invoice
        section: admin
        templates: SyliusAdminBundle:Crud
        only: ['index']
        grid: sylius_invoicing_plugin_invoice
        permission: true
        vars:
            all:
                subheader: sylius_invoicing_plugin.ui.manage_invoices
            index:
                icon: inbox
    type: sylius.resource
```

Another aspect that can be both replaced and customized is displaying Invoices list on Order show view.
Code responsible for displaying Invoices related to the Order is injected to existing Sylius template using
Sonata events. You can read about customizing templates via events here:

<http://docs.sylius.com/en/1.2/customization/template.html>
