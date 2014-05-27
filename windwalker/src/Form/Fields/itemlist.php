<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Windwalker\DI\Container;
use Windwalker\Helper\HtmlHelper;
use Windwalker\Helper\LanguageHelper;
use Windwalker\Helper\ModalHelper;

// No direct access
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

include_once JPATH_LIBRARIES . '/windwalker/src/init.php';

/**
 * Supports a HTML select list for target items.
 *
 * @since 2.0
 */
class JFormFieldItemlist extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Itemlist';

	/**
	 * The value of the form field.
	 *
	 * @var  mixed
	 */
	protected $value = null;

	/**
	 * The name of the form field.
	 *
	 * @var  string
	 */
	protected $name = null;

	/**
	 * List name.
	 *
	 * @var string
	 */
	protected $view_list = null;

	/**
	 * Item name.
	 *
	 * @var string
	 */
	protected $view_item = null;

	/**
	 * Extension name, eg: com_content.
	 *
	 * @var string
	 */
	protected $extension = null;

	/**
	 * Component name without ext type, eg: content.
	 *
	 * @var string
	 */
	protected $component = null;

	/**
	 * Set the published column name in table.
	 *
	 * @var string
	 */
	protected $published_field = 'state';

	/**
	 * Set the ordering column name in table.
	 *
	 * @var string
	 */
	protected $ordering_field = null;

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		return parent::getInput() . $this->quickadd();
	}

	/**
	 * Method to get the list of files for the field options.
	 * Specify the target directory with a directory attribute
	 * Attributes allow an exclude mask and stripping of extensions from file name.
	 * Default attribute may optionally be set to null (no file) or -1 (use a default).
	 *
	 * @return  array  The field option objects.
	 */
	public function getOptions()
	{
		// Initialise variables.
		// ========================================================================
		$this->setElement();
		$options     = array();
		$name        = (string) $this->element['name'];
		$key_field   = $this->element['key_field'] ? (string) $this->element['key_field'] : 'id';
		$value_field = $this->element['value_field'] ? (string) $this->element['value_field'] : 'title';
		$show_root   = (string) $this->element['show_root'] ? $this->element['show_root'] : false;
		$nested      = (string) $this->element['nested'];

		$items = $this->getItems();

		// Set Options
		// ========================================================================
		foreach ($items as $item)
		{
			$item  = new JObject($item);
			$level = !empty($item->level) ? $item->level - 1 : 0;

			if ($level < 0)
			{
				$level = 0;
			}

			$options[] = JHtml::_('select.option', $item->$key_field, str_repeat('- ', $level) . $item->$value_field);
		}

		// Verify permissions.  If the action attribute is set, then we scan the options.
		// ========================================================================
		if ((string) $this->element['action'] || (string) $this->element['access'])
		{
			$options = $this->permissionCheck($options);
		}

		// Show root
		// ========================================================================
		if ($show_root)
		{
			array_unshift($options, JHtml::_('select.option', 1, JText::_('JGLOBAL_ROOT')));
		}

		// Merge any additional options in the XML definition.
		// ========================================================================
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * Use Query to get Items.
	 *
	 * @return \stdClass[]
	 */
	public function getItems()
	{
		$published   = (string) $this->element['published'];
		$nested      = (string) $this->element['nested'];
		$key_field   = $this->element['key_field'] ? (string) $this->element['key_field'] : 'id';
		$value_field = $this->element['value_field'] ? (string) $this->element['value_field'] : 'title';
		$ordering    = $this->element['ordering'] ? (string) $this->element['ordering'] : null;
		$table_name  = $this->element['table'] ? (string) $this->element['table'] : '#__' . $this->component . '_' . $this->view_list;
		$select      = $this->element['select'];

		$container = Container::getInstance();
		$db    = $container->get('db');
		$q     = $db->getQuery(true);
		$input = $container->get('input');

		// Avoid self
		// ========================================================================
		$id     = $input->get('id');
		$option = $input->get('option');
		$view   = $input->get('view');
		$layout = $input->get('layout');

		if ($nested && $id)
		{
			$table = JTable::getInstance(ucfirst($this->view_item), ucfirst($this->component) . 'Table');
			$table->load($id);
			$q->where("id != {$id}");
			$q->where("lft < {$table->lft} OR rgt > {$table->rgt}");
		}

		if ($nested)
		{
			$q->where("( id != 1 AND `{$value_field}` != 'ROOT' )");
		}

		// Some filter
		// ========================================================================
		if ($published)
		{
			$q->where("{$this->published_field} >= 1");
		}

		// Ordering
		$order    = $nested ? 'lft' : 'id';
		$order    = $this->ordering_field ? $this->ordering_field : $order;
		$ordering = $ordering ? $ordering : $order;

		if ($ordering != 'false')
		{
			$q->order($ordering);
		}

		// Query
		// ========================================================================
		$select = $select ? '*, ' . $select : '*';

		$q->select($select)
			->from($table_name);

		$db->setQuery($q);
		$items = $db->loadObjectList();

		$items = $items ? $items : array();

		return $items;
	}

	/**
	 * Check ACL permissions. If not permitted, remove this option.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function permissionCheck($options)
	{
		// Get the current user object.
		$user = Container::getInstance()->get('user');

		// For new items we want a list of categories you are allowed to create in.
		if (!$this->value)
		{
			foreach ($options as $i => $option)
			{
				/*
				 * To take save or create in a category you need to have create rights for that category
				 * unless the item is already in that category.
				 * Unset the option if the user isn't authorised for it. In this field assets are always categories.
				 */
				if ($user->authorise('core.create', $this->extension . '.' . $this->view_item . '.' . $option->value) != true)
				{
					unset($options[$i]);
				}
			}
		}

		// If you have an existing category id things are more complex.
		else
		{
			$value = $this->value;

			foreach ($options as $i => $option)
			{
				// If you are only allowed to edit in this category but not edit.state, you should not get any
				// option to change the category.
				if ($user->authorise('core.edit.own', $this->extension . '.' . $this->view_item . '.' . $value) != true)
				{
					if ($option->value != $value)
					{
						unset($options[$i]);
					}
				}

				// However, if you can edit.state you can also move this to another category for which you have
				// create permission and you should also still be able to save in the current category.
				elseif (($user->authorise('core.create', $this->extension . '.' . $this->view_item . '.' . $option->value) != true)
					&& $option->value != $value)
				{
					unset($options[$i]);
				}
			}
		}

		return $options;
	}

	/**
	 * Add an quick add button & modal
	 *
	 * @return string
	 */
	public function quickadd()
	{
		// Prepare Element
		$readonly = $this->getElement('readonly', false);
		$disabled = $this->getElement('disabled', false);

		if ($readonly || $disabled)
		{
			return false;
		}

		$task             = $this->getElement('task', $this->view_item . '.ajax.quickadd');
		$quickadd         = $this->getElement('quickadd', false);
		$table_name       = $this->getElement('table', '#__' . $this->component . '_' . $this->view_list);
		$key_field        = $this->getElement('key_field', 'id');
		$value_field      = $this->getElement('value_field', 'title');
		$formpath         = $this->getElement('quickadd_formpath', "administrator/components/{$this->extension}/model/form/{$this->view_item}.xml");
		$quickadd_handler = $this->getElement('quickadd_handler', $this->extension);
		$title            = $this->getElement('quickadd_label', 'LIB_WINDWALKER_QUICKADD_TITLE');

		$qid = $this->id . '_quickadd';

		if (!$quickadd)
		{
			return '';
		}

		// Prepare Script & Styles
		/** @var Windwalker\Helper\AssetHelper $asset */
		$asset = Container::getInstance($quickadd_handler)->get('helper.asset');
		$asset->addJs('js/quickadd.js');

		// Set AKQuickAddOption
		$config['task']             = $task;
		$config['quickadd_handler'] = $quickadd_handler;
		$config['extension']        = $this->extension;
		$config['component']        = $this->component;
		$config['table']            = $table_name;
		$config['model_name']       = $this->view_item;
		$config['key_field']        = $key_field;
		$config['value_field']      = $value_field;
		$config['joomla3']          = (JVERSION >= 3);

		$config = HtmlHelper::getJSObject($config);

		$script = <<<QA
        window.addEvent('domready', function(){
            var AKQuickAddOption = {$config} ;
            AKQuickAdd.init('{$qid}', AKQuickAddOption);
        });
QA;

		$asset->internalJS($script);

		// Load Language & Form
		LanguageHelper::loadLanguage('com_' . $this->component, null);

		$formpath = str_replace(JPATH_ROOT, '', $formpath);
		$content  = ModalHelper::getQuickaddForm($qid, $formpath, (string) $this->element['extension']);

		// Prepare HTML
		$html         = '';
		$button_title = $title;
		$modal_title  = $button_title;
		$button_class = 'btn btn-small btn-success delicious green light fltlft quickadd_button';

		$footer = "<button class=\"btn delicious\" type=\"button\" onclick=\"$$('#{$qid} input', '#{$qid} select').set('value', '');AKQuickAdd.closeModal('{$qid}');\" data-dismiss=\"modal\">" . JText::_('JCANCEL') . "</button>";
		$footer .= "<button class=\"btn btn-primary delicious blue\" type=\"submit\" onclick=\"AKQuickAdd.submit('{$qid}', event);\">" . JText::_('JSUBMIT') . "</button>";

		$html .= ModalHelper::modalLink(JText::_($button_title), $qid, array('class' => $button_class, 'icon' => 'icon-new icon-white'));
		$html .= ModalHelper::renderModal($qid, $content, array('title' => JText::_($modal_title), 'footer' => $footer));

		return $html;
	}

	/**
	 * Set some element attributes to class variable.
	 *
	 * @return JFormField
	 */
	public function setElement()
	{
		$view_item = (string) $this->element['view_item'];
		$view_list = (string) $this->element['view_list'];
		$extension = (string) $this->element['extension'];

		if (!empty($view_item))
		{
			$this->view_item = $view_item;
		}

		if (!empty($view_list))
		{
			$this->view_list = $view_list;
		}

		if (!empty($extension))
		{
			$this->extension = $extension;
		}

		$this->component = str_replace('com_', '', $this->extension);

		return $this;
	}

	/**
	 * Get Element Value.
	 *
	 * @param string $key     Element attribute key.
	 * @param mixed  $default The default value if not exists.
	 *
	 * @return string The attribute value.
	 */
	public function getElement($key, $default = null)
	{
		if ($this->element[$key])
		{
			return (string) $this->element[$key];
		}
		else
		{
			return $default;
		}
	}
}
