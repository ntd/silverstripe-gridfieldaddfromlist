<?php

namespace eNTiDi\GridFieldAddFromList;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_DataManipulator;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;

/**
 * `GridField` component that allows to add a new row, in a similar way
 * of what already done by stock `GridFieldAddExistingAutocompleter` but
 * using a custom `DropdownField` instead of an autocompleter entry.
 */
class GridFieldAddFromList implements GridField_HTMLProvider, GridField_ActionProvider, GridField_DataManipulator
{
    /**
     * @var string The HTML fragment to write this component into
     */
    protected $fragment;

    /**
     * @var string Which field must be set
     */
    protected $field;

    /**
     * @var SS_List List of available options
     */
    protected $list;

    /**
     * @var string Text shown by default on the dropdown field
     */
    protected $placeholder;


    /**
     * @param string $fragment  Fragment where to render this component
     * @param string $field     Target field, defaults to `ID`
     * @param SS_List $list     Source list for the dropdown
     */
    public function __construct($fragment = 'toolbar-header-left', $field = 'ID', SS_List $list = null)
    {
        $this->fragment = $fragment;
        $this->field = $field;
        $this->list = $list;
        $this->placeholder = _t(self::class . '.PLACEHOLDER', '(select an option)');
    }

    /**
     * @param GridField $grid
     * @return string[] - HTML
     */
    public function getHTMLFragments($grid)
    {
        $list = $this->list;
        if (! $list) {
            // Fallback to the full model class without GridField items
            $list = $grid->getModelClass()::get()
                ->subtract($grid->getList());
        }

        $dropdown = DropdownField::create('gridfield_addfromlist_original')
            ->setSource($list->map('ID', 'Title'))
            ->addExtraClass('chosen form-control no-change-track');
        if ($this->placeholder) {
            $dropdown->setEmptyString($this->placeholder);
        }

        $action = GridField_FormAction::create(
            $grid,
            'gridfield_relationadd',
            _t(self::class . '.ADDTO', 'Add row'),
            'addto',
            'addto'
        )   ->setDisabled(true)
            ->addExtraClass('btn btn-primary font-icon-plus-circled action_gridfield_relationadd');

        $data = ArrayData::create([
            'Dropdown' => $dropdown,
            'Action'   => $action,
        ]);

        $templates = SSViewer::get_templates_by_class(__CLASS__);
        return [
            $this->fragment => $data->renderWith($templates)
        ];
    }

    /**
     * @param GridField $grid
     * @return array
     */
    public function getActions($grid)
    {
        return [ 'addto' ];
    }

    /**
     * Manipulate the state to add a new relation
     *
     * @param GridField $grid
     * @param string $action Action identifier, see {@link getActions()}.
     * @param array $arguments Arguments relevant for this
     * @param array $data All form data
     */
    public function handleAction(GridField $grid, $action, $arguments, $data)
    {
        // `gridfield_addfromlist` is an hidden field generated client
        // side whenever an option is selected in the dropdown field
        switch ($action) {
            case 'addto':
                if (isset($data['gridfield_addfromlist']) && $data['gridfield_addfromlist']) {
                    $grid->State->GridFieldAddRelation = $data['gridfield_addfromlist'];
                }
                break;
        }
    }

    /**
     * If an object ID is set, add the object to the list.
     *
     * @param GridField $grid
     * @param SS_List $list
     * @return SS_List
     */
    public function getManipulatedData(GridField $grid, SS_List $list)
    {
        $id = $grid->State->GridFieldAddRelation(null);
        if (! empty($id)) {
            $object = $list->newObject([
                $this->field => $id,
            ]);
            $list->add($object);
            $grid->State->GridFieldAddRelation = null;
        }
        return $list;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setTargetField($name)
    {
        $this->field = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTargetField()
    {
        return $this->field;
    }

    /**
     * @param array $list
     * @return $this
     */
    public function setSearchList(SS_List $list)
    {
        $this->list = $list;
        return $this;
    }

    /**
     * @return SS_List|null
     */
    public function getSearchList()
    {
        return $this->list;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setPlaceholderOption($text)
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlaceholderOption()
    {
        return $this->placeholder;
    }
}
