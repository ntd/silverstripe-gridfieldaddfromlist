Usage
-----

By default, `GridFieldAddFromList` acts as a drop-in replacement for
`GridFieldAddExistingAutocompleter` but using a stock `DropdownField`.

The following example shows a typical usage of this component. Some of
the most common options are documented in the comments.

```php
use eNTiDi\GridFieldAddFromList\GridFieldAddFromList;
use SilverStripe\Form\GridField\GridFieldAddNewButton;
use SilverStripe\ORM\DataObject;

class Document extends DataObject
{
    private static $db = [
        'Name' => 'Varchar',
    ];

    private static $has_many = [
        'Rows' => DocumentArticle::class,
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $grid = $fields->fieldByName('Root.Rows.Rows');
        if ($grid) {
            // Allow to add new rows only after selecting an article
            $component = new GridFieldAddFromList;
            $grid->getConfig()
                 ->removeComponentsByType([ GridFieldAddNewButton::class ])
                 ->addComponent($component);

            // If you are using `$many_many_extraFields` instead, the
            // default target field (`ID`) will just work as expected
            $component->setTargetField('ArticleID');

            // To be able to add an article more than once, you need to
            // reset the unique flag (it is set by default), i.e.:
            // $component->setUnique(false);

            // If needed, you can use a custom list, e.g.:
            // $component->setSearchList(Article::get()->filter([
            //     'Stock:GreaterThan' => 0,
            // ]));
        }

        return $fields;
    }
}

class Article extends DataObject
{
    private static $db = [
        'Name'  => 'Varchar',
        'Stock' => 'Int',
    ];
    private static $has_many = [
        'Rows' => DocumentArticle::class,
    ];
}

class DocumentArticle extends DataObject
{
    private static $db = [
        'Quantity' => 'Int',
        'Price'    => 'Decimal(5,2)',
    ];
    private static $has_one = [
        'Document' => Document::class,
        'Article'  => Article::class,
    ];
    private static $summary_fields = [
       'Article.Name',
       'Quantity',
       'Price',
    ];
}
```
