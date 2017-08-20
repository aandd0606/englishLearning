<?php

namespace Instante\Bootstrap3Renderer;

use Nette;
use Nette\Application\UI\ITemplate;
use Nette\Forms\Controls;
use Nette\Bridges\FormsLatte\FormMacros;
use Nette\Utils\Html;

if (!class_exists('Nette\Bridges\FormsLatte\FormMacros')) {
    class_alias('Nette\Latte\Macros\FormMacros', 'Nette\Bridges\FormsLatte\FormMacros');
}

/**
 * Created with twitter bootstrap in mind.
 *
 * <code>
 * $form->setRenderer(new Instante\Bootstrap3Renderer\BootstrapRenderer);
 * </code>
 *
 * @author Pavel Ptacek
 * @author Filip Procházka
 * @author Richard Ejem
 * @author Ondrej Hubsch
 */
class BootstrapRenderer extends Nette\Object implements Nette\Forms\IFormRenderer
{

    const MODE_HORIZONTAL = 'form-horizontal';
    const MODE_VERTICAL = NULL;
    const MODE_INLINE = 'form-inline';
    const MODE_NO_CLASS = self::MODE_VERTICAL;

    public static $checkboxListClasses
        = array(
            'Nextras\Forms\Controls\MultiOptionList',
            'Nette\Forms\Controls\CheckboxList',
            'Kdyby\Forms\Controls\CheckboxList',
        );

    public static $textBaseClasses
        = array(
            'Nette\Forms\Controls\TextBase',
            'Vodacek\Forms\Controls\DateInput',
        );

    /** @var int */
    private $labelColumns = 2;

    /** @var int */
    private $inputColumns = 10;

    /** @var string */
    private $columnClassPrefix = 'col-sm-';

    /** @var boolean */
    private $horizontalMode = FALSE;

    /**
     * set to false, if you want to display the field errors also as form errors
     * @var bool
     */
    public $errorsAtInputs = TRUE;

    /**
     * Groups that should be rendered first
     */
    public $priorGroups = array();

    /**
     * @var \Nette\Forms\Form
     */
    private $form;

    /** @var string Enumeration of self::MODE_* */
    private $mode = self::MODE_HORIZONTAL;

    /**
     * @var \Nette\Templating\Template|\stdClass
     */
    private $template;

    /**
     * @param ITemplate $template
     */
    public function __construct(ITemplate $template = NULL)
    {
        $this->template = $template;
    }

    /**
     * Render the templates
     *
     * @param \Nette\Forms\Form $form
     * @param string $mode
     * @param array $args
     * @return void
     */
    public function render(Nette\Forms\Form $form, $mode = NULL, $args = NULL)
    {
        if ($this->template === NULL) {
            if ($presenter = $form->lookup('Nette\Application\UI\Presenter', FALSE)) {
                /** @var \Nette\Application\UI\Presenter $presenter */
                $this->template = clone $presenter->getTemplate();
            } else {
                $this->template = new Nette\Bridges\ApplicationLatte\Template(new Nette\Latte\Engine);
            }
        }

        if ($this->form !== $form) {
            $this->form = $form;

            // translators
            if ($translator = $this->form->getTranslator()) {
                $this->template->setTranslator($translator);
            }

            // controls placeholders & classes
            foreach ($this->form->getControls() as $control) {
                $this->prepareControl($control);
            }

            $formEl = $form->getElementPrototype();
            if (!($classes = self::getClasses($formEl)) || stripos($classes, 'form-') === FALSE) {
                $this->horizontalMode = $this->mode === self::MODE_HORIZONTAL;
                if ($this->mode !== self::MODE_NO_CLASS) {
                    $formEl->addClass($this->mode);
                }
            }
        } elseif ($mode === 'begin') {
            foreach ($this->form->getControls() as $control) {
                /** @var \Nette\Forms\Controls\BaseControl $control */
                $control->setOption('rendered', FALSE);
            }
        }

        $this->template->setFile(__DIR__ . '/@form.latte');
        $this->template->setParameters(
            array_fill_keys(array('control', '_control', 'presenter', '_presenter'), NULL) +
            array('_form' => $this->form, 'form' => $this->form, 'renderer' => $this)
        );

        if ($this->horizontalMode) {
            $this->template->labelCols = $this->labelColumns;
            $this->template->inputCols = $this->inputColumns;
            $this->template->labelClass = $this->columnClassPrefix . $this->labelColumns;
            $this->template->inputClass = $this->columnClassPrefix . $this->inputColumns;
            $this->template->skipClass = $this->columnClassPrefix . 'offset-' . $this->labelColumns;
        }

        if ($mode === NULL) {
            if ($args) {
                $this->form->getElementPrototype()->addAttributes($args);
            }
            $this->template->render();
        } elseif ($mode === 'begin') {
            FormMacros::renderFormBegin($this->form, (array)$args);
        } elseif ($mode === 'end') {
            FormMacros::renderFormEnd($this->form);
        } else {
            $attrs = array('input' => array(), 'label' => array(), 'pair' => array(), 'pair-class' => '');

            foreach ((array)$args as $key => $val) {
                if (stripos($key, 'input-') === 0) {
                    $attrs['input'][substr($key, 6)] = $val;
                } elseif (stripos($key, 'label-') === 0) {
                    $attrs['label'][substr($key, 6)] = $val;
                } elseif ($key === 'class') {
                    $attrs['pair-class'] = $val;
                } else {
                    $attrs['pair'][$key] = $val;
                }
            }

            if ($this->horizontalMode) {
                if (isset($attrs['label']['class'])) {
                    $attrs['label']['class'] .= ' ' . $this->columnClassPrefix . $this->labelColumns;
                } else {
                    $attrs['label']['class'] = $this->columnClassPrefix . $this->labelColumns;
                }
            }

            $this->template->setFile(__DIR__ . '/@parts.latte');
            $this->template->mode = $mode;
            $this->template->attrs = (array)$attrs;
            $this->template->render();
        }
    }

    /**
     * @param \Nette\Forms\Controls\BaseControl $control
     */
    private function prepareControl(Controls\BaseControl $control)
    {
        $translator = $this->form->getTranslator();
        $control->setOption('rendered', FALSE);

        if ($control->isRequired()) {
            $control->getLabelPrototype()->addClass('required');
            $control->setOption('required', TRUE);
        }

        $el = $control->getControlPrototype();

        if ($placeholder = $control->getOption('placeholder')) {
            if (!$placeholder instanceof Html && $translator) {
                $placeholder = $translator->translate($placeholder);
            }
            $el->placeholder($placeholder);
        }

        if ($control->controlPrototype->type === 'email' && $control->getOption('input-prepend') === NULL
        ) {
            $control->setOption('input-prepend', '@');
        }

        if ($control instanceof Nette\Forms\ISubmitterControl) {
            $el->addClass('btn');

            if ($control->getOption('btn-class') !== NULL) {
                $el->addClass($control->getOption('btn-class'));
            } else {
                $el->addClass('btn-default');
            }
        } else {

            if (static::isTextBase($control) || $control instanceof Controls\SelectBox) {
                $classes = $control->controlPrototype->class;
                if (!is_array($classes)) {
                    $classes = explode(' ', $classes);
                }
                if (($pos = array_search('no-form-control', $classes, TRUE)) !== FALSE) {
                    unset($classes[$pos]);
                    $el->class = $classes;
                } elseif ($control->getOption('noFormControl', FALSE) !== TRUE) {
                    $el->addClass('form-control');
                }
            }

            $label = $control->labelPrototype;
            if (!$control instanceof Controls\Checkbox && !$control instanceof Controls\RadioList
                && !static::isCheckboxList($control)
            ) {
                $label->addClass('control-label');
            }

            $control->setOption('pairContainer', $pair = Html::el('div'));
            $pair->id = $control->htmlId . '-pair';
            $pair->addClass('form-group');


            if ($control->getOption('required', FALSE)) {
                $pair->addClass('required');
            }
            if ($control->errors) {
                $pair->addClass('has-error');
            }
        }
    }

    /**
     * @return array
     */
    public function findErrors()
    {
        $formErrors = $this->errorsAtInputs ? $this->form->getOwnErrors() : $this->form->getErrors();

        if (!$formErrors) {
            return array();
        }

        $form = $this->form;
        $translate = function ($errors) use ($form) {
            if ($translator = $form->getTranslator()) { // If we have translator, translate!
                foreach ($errors as $key => $val) {
                    $errors[$key] = $translator->translate($val);
                }
            }

            return $errors;
        };

        return $translate($formErrors);
    }

    /**
     * @throws \RuntimeException
     * @return object[]
     */
    public function findGroups()
    {
        $formGroups = $visitedGroups = array();
        foreach ($this->priorGroups as $i => $group) {
            if (!$group instanceof Nette\Forms\ControlGroup) {
                if (!$group = $this->form->getGroup($group)) {
                    $groupName = (string)$this->priorGroups[$i];
                    throw new \RuntimeException("Form has no group $groupName.");
                }
            }

            $visitedGroups[] = $group;
            if ($group = $this->processGroup($group)) {
                $formGroups[] = $group;
            }
        }

        foreach ($this->form->getGroups() as $group) {
            if (!in_array($group, $visitedGroups, TRUE) && ($group = $this->processGroup($group))) {
                $formGroups[] = $group;
            }
        }

        return $formGroups;
    }

    /**
     * @param \Nette\Forms\Container $container
     * @param boolean $buttons
     * @return \Iterator
     */
    public function findControls(Nette\Forms\Container $container = NULL, $buttons = NULL)
    {
        $container = $container ?: $this->form;
        return new \CallbackFilterIterator($container->getControls(), function ($control) use ($buttons) {
            $control = $control instanceof Filter ? $control->current() : $control;
            $isButton = $control instanceof Controls\Button || $control instanceof Nette\Forms\ISubmitterControl;
            return !$control->getOption('rendered') && !$control instanceof Controls\HiddenField
            && (($buttons === TRUE
                    && $isButton)
                || ($buttons === FALSE && !$isButton)
                || $buttons === NULL);
        });
    }

    /**
     * @internal
     * @param \Nette\Forms\ControlGroup $group
     * @return object
     */
    public function processGroup(Nette\Forms\ControlGroup $group)
    {
        if (!$group->getOption('visual') || !$group->getControls()) {
            return NULL;
        }

        $groupLabel = $group->getOption('label');
        $groupDescription = $group->getOption('description');

        // If we have translator, translate!
        if ($translator = $this->form->getTranslator()) {
            if (!$groupLabel instanceof Html) {
                $groupLabel = $translator->translate($groupLabel);
            }
            if (!$groupDescription instanceof Html) {
                $groupDescription = $translator->translate($groupDescription);
            }
        }

        $controls = array_filter($group->getControls(), function (Controls\BaseControl $control) {
            return !$control->getOption('rendered') && !$control instanceof Controls\HiddenField;
        });

        if (!$controls) {
            return NULL; // do not render empty groups
        }

        $groupAttrs = $group->getOption('container', Html::el())->setName(NULL);
        /** @var Html $groupAttrs */
        $groupAttrs->attrs += array_diff_key($group->getOptions(), array_fill_keys(array(
            'container',
            'label',
            'description',
            'visual',
            'template', // these are not attributes
        ), NULL));

        // fake group
        return (object)(array(
                'controls' => $controls,
                'label' => $groupLabel,
                'description' => $groupDescription,
                'attrs' => $groupAttrs,
            ) + $group->getOptions());
    }

    /**
     *  @internal
     * @param \Nette\Forms\Controls\BaseControl $control
     * @return string
     */
    public static function getControlName(Controls\BaseControl $control)
    {
        return $control->lookupPath('Nette\Forms\Form');
    }

    /**
     *  @internal
     * @param \Nette\Forms\Controls\BaseControl $control
     * @return \Nette\Utils\Html
     */
    public static function getControlDescription(Controls\BaseControl $control)
    {
        if (!$desc = $control->getOption('description')) {
            return Html::el();
        }

        // If we have translator, translate!
        if (!$desc instanceof Html && ($translator = $control->form->getTranslator())) {
            $desc = $translator->translate($desc); // wtf?
        }

        // create element
        return Html::el('p', array('class' => 'help-block'))
            ->{$desc instanceof Html ? 'add' : 'setText'}($desc);
    }

    /**
     *  @internal
     * @param \Nette\Forms\Controls\BaseControl $control
     * @return \Nette\Utils\Html
     */
    public function getControlError(Controls\BaseControl $control)
    {
        if (!($errors = $control->getErrors()) || !$this->errorsAtInputs) {
            return Html::el();
        }
        $error = reset($errors);

        // If we have translator, translate!
        if (!$error instanceof Html && ($translator = $control->form->getTranslator())) {
            $error = $translator->translate($error); // wtf?
        }

        // create element
        return Html::el('p', array('class' => 'text-danger'))
            ->{$error instanceof Html ? 'add' : 'setText'}($error);
    }

    /**
     *  @internal
     * @param \Nette\Forms\Controls\BaseControl $control
     * @return string
     */
    public static function getControlTemplate(Controls\BaseControl $control)
    {
        return $control->getOption('template');
    }

    /**
     *  @internal
     * @param \Nette\Forms\IControl $control
     * @return bool
     */
    public static function isButton(Nette\Forms\IControl $control)
    {
        return $control instanceof Controls\Button;
    }

    /**
     *  @internal
     * @param \Nette\Forms\IControl $control
     * @return bool
     */
    public static function isSubmitButton(Nette\Forms\IControl $control = NULL)
    {
        return $control instanceof Nette\Forms\ISubmitterControl;
    }

    /**
     *  @internal
     * @param \Nette\Forms\IControl $control
     * @return bool
     */
    public static function isCheckbox(Nette\Forms\IControl $control)
    {
        return $control instanceof Controls\Checkbox;
    }

    /**
     *  @internal
     * @param \Nette\Forms\IControl $control
     * @return bool
     */
    public static function isRadioList(Nette\Forms\IControl $control)
    {
        return $control instanceof Controls\RadioList;
    }

    /**
     * @internal
     * @param \Nette\Forms\IControl $control
     * @return bool
     */
    public static function isCheckboxList(Nette\Forms\IControl $control)
    {
        foreach (static::$checkboxListClasses as $class) {
            if (class_exists($class, FALSE) && $control instanceof $class) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * @internal
     * @param \Nette\Forms\IControl $control
     * @return bool
     */
    public static function isTextBase(Nette\Forms\IControl $control)
    {
        foreach (static::$textBaseClasses as $class) {
            if (class_exists($class, FALSE) && $control instanceof $class) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * @internal
     * @param \Nette\Forms\Controls\RadioList $control
     * @return bool
     */
    public static function getRadioListItems(Controls\RadioList $control)
    {
        $items = array();
        if (count($control->items) === 0) {
            $control->getControl(); //sets rendered flag to control if emtpy
        }
        foreach ($control->items as $key => $value) {
            $el = $control->getControlPart($key);
            if ($el->getName() === 'input') {
                $items[$key] = $radio = (object)array(
                    'input' => $el,
                    'label' => $cap = $control->getLabelPart($key),
                    'caption' => $cap->getText(),
                );
            } else {
                $items[$key] = $radio = (object)array(
                    'input' => $el[0],
                    'label' => $el[1],
                    'caption' => $el[1]->getText(),
                );
            }

            $radio->html = clone $radio->label;
            $radio->html->insert(0, $radio->input);
        }

        return $items;
    }

    /**
     * @internal
     * @param \Nette\Forms\Controls\BaseControl $control
     * @throws \Nette\InvalidArgumentException
     * @return bool
     */
    public static function getCheckboxListItems(Controls\BaseControl $control)
    {
        $items = array();
        if (count($control->items) === 0) {
            $control->getControl(); //sets rendered flag to control if emtpy
        }
        foreach ($control->items as $key => $value) {
            if (method_exists($control, 'getControlPart')) {
                $el = $control->getControlPart($key);
                $items[$key] = $check = (object)array(
                    'input' => $el,
                    'label' => $cap = $control->getLabelPart($key),
                    'caption' => $cap->getText(),
                );
            } else {
                $el = $control->getControl($key);
                if (is_string($el)) {
                    $items[$key] = $check = (object)array(
                        'input' => Html::el()->setHtml($el),
                        'label' => Html::el(),
                        'caption' => $value,
                    );
                } else {
                    $items[$key] = $check = (object)array(
                        'input' => $el[0],
                        'label' => $el[1],
                        'caption' => $el[1]->getText(),
                    );
                }
            }
            $check->html = clone $check->label;
            $display = $control->getOption('display', 'inline');
            if ($display == 'inline') {
                $check->html->addClass($display);
            }
            $check->html->insert(0, $check->input);
        }

        return $items;
    }

    /**
     * @return string
     */
    public function getColumnClassPrefix()
    {
        return $this->columnClassPrefix;
    }

    /**
     * @param string $prefix
     * @return self fluent interface
     */
    public function setColumnClassPrefix($prefix)
    {
        $this->columnClassPrefix = $prefix;
        return $this;
    }

    /**
     * @param \Nette\Forms\Controls\BaseControl $control
     * @return \Nette\Utils\Html
     */
    public static function getLabelBody(Controls\BaseControl $control)
    {
        $label = $control->getLabel();
        return $label;
    }

    /**
     * @param \Nette\Forms\Controls\BaseControl $control
     * @param string $class
     * @return bool
     */
    public static function controlHasClass(Controls\BaseControl $control, $class)
    {
        $classes = explode(' ', self::getClasses($control->controlPrototype));
        return in_array($class, $classes, TRUE);
    }

    /**
     * @param \Nette\Utils\Html $_this
     * @param array $attrs
     * @return \Nette\Utils\Html
     */
    public static function mergeAttrs(Html $_this = NULL, array $attrs)
    {
        if ($_this === NULL) {
            return Html::el();
        }

        $_this->attrs = array_merge_recursive($_this->attrs, $attrs);
        return $_this;
    }

    /**
     * @param \Nette\Utils\Html $el
     * @return bool
     */
    private static function getClasses(Html $el)
    {
        if (is_array($el->class)) {
            $classes = array_filter(array_merge(array_keys($el->class), $el->class), 'is_string');
            return implode(' ', $classes);
        }
        return $el->class;
    }

    /**
     * @return bool
     */
    public function isHorizontalMode()
    {
        return $this->horizontalMode;
    }

    /**
     * @return int
     */
    public function getLabelColumns()
    {
        return $this->labelColumns;
    }

    /**
     * @param int $cols
     * @return self fluent interface
     */
    public function setLabelColumns($cols)
    {
        $this->labelColumns = (int)$cols;
        return $this;
    }

    /**
     * @return int
     */
    public function getInputColumns()
    {
        return $this->inputColumns;
    }

    /**
     * @param int $cols
     * @return self fluent interface
     */
    public function setInputColumns($cols)
    {
        $this->inputColumns = (int)$cols;
        return $this;
    }

    /** @return string */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }
}
