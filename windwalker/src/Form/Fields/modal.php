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

include_once JPATH_LIBRARIES . '/windwalker/src/init.php';

/**
 * Supports a Modal picker for target items.
 *
 * @since 2.0
 */
class JFormFieldModal extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Modal';

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
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	public function getInput()
	{
		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal');
		$this->setElement();
		$this->setScript();

		// Setup variables for display.
		$readonly = $this->getElement('readonly', false);
		$disabled = $this->getElement('disabled', false);
		$html     = array();
		$link     = $this->getLink();
		$title    = $this->getTitle();

		if (empty($title))
		{
			$title = $this->element['select_label']
				? (string) JText::_($this->element['select_label'])
				: JText::_('COM_' . strtoupper($this->component) . '_SELECT_ITEM');
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current user display field.
		$html[] = '<span class="' . (!$disabled && !$readonly ? 'input-append' : '') . '">';
		$html[] = '<input type="text" class="' . (!$disabled && !$readonly ? 'input-medium ' . $this->element['class'] : $this->element['class']) . '" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';

		if (!$disabled && !$readonly)
		{
			$html[] = '<a class="modal btn" title="' . JText::_('COM_' . strtoupper($this->component) . '_CHANGE_ITEM_BUTTON') . '"  href="' . $link . '&amp;' . JSession::getFormToken() . '=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . JText::_('JSELECT') . '</a>';
		}

		$html[] = '</span>';

		// The active article id field.
		if (!$this->value)
		{
			$value = '';
		}
		else
		{
			$value = $this->value;
		}

		// Class='required' for client side validation
		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html) . $this->quickadd();
	}

	/**
	 * Set Script.
	 *
	 * @return void
	 */
	public function setScript()
	{
		// Build the script.
		$script   = array();
		$script[] = '    function jSelect' . ucfirst($this->component) . '_' . $this->id . '(id, title) {';
		$script[] = '        document.id("' . $this->id . '_id").value = id;';
		$script[] = '        document.id("' . $this->id . '_name").value = title;';
		$script[] = '        SqueezeBox.close();';
		$script[] = '    }';

		// Add the script to the document head.
		$asset = Container::getInstance()->get('helper.asset');
		$asset->internalJS(implode("\n", $script));
	}

	/**
	 * Set some element attributes to class variable.
	 *
	 * @return void
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
	}

	/**
	 * Get item title.
	 *
	 * @return string
	 */
	public function getTitle()
	{
		$ctrl        = $this->view_list;
		$title_field = $this->element['title_field'] ? (string) $this->element['title_field'] : 'title';

		/** @var $db JDatabaseDriver */
		$container = Container::getInstance();
		$db = $container->get('db');
		$q  = $db->getQuery(true);

		$q->select($title_field)
			->from('#__' . $this->component . '_' . $ctrl)
			->where("id = '{$this->value}'");

		$db->setQuery($q);

		try
		{
			$title = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			$container->get('app')->enqueueMessage(get_class($this) . ': ' . $e->getMessage(), 'error');

			return '';
		}

		return $title;
	}

	/**
	 * Get item link.
	 *
	 * @return string The link string.
	 */
	public function getLink()
	{
		// Avoid self
		$input  = Container::getInstance()->get('input');
		$id     = $input->get('id');
		$option = $input->get('option');
		$view   = $input->get('view');
		$layout = $input->getString('layout');
		$params = '';

		if (isset($this->element['show_root']))
		{
			$params .= '&show_root=1';
		}

		if ($view == $this->view_item && $option == $this->extension && $layout == 'edit' && $id)
		{
			$params .= '&avoid=' . $id;
		}

		return 'index.php?option=' . $this->extension . '&view=' . $this->view_list . $params
			. '&layout=modal&tmpl=component&function=jSelect' . ucfirst($this->component) . '_' . $this->id;
	}

	/**
	 * Add an quick add button & modal
	 *
	 * @return mixed
	 */
	public function quickadd()
	{
		// Prepare Element
		$readonly = $this->getElement('readonly', false);
		$disabled = $this->getElement('disabled', false);

		if ($readonly || $disabled)
		{
			return;
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
		$button_class = 'btn btn-small btn-success delicious green light fltlft quickadd_buttons';

		$footer = "<button class=\"btn delicious\" type=\"button\" onclick=\"$$('#{$qid} input', '#{$qid} select').set('value', '');AKQuickAdd.closeModal('{$qid}');\" data-dismiss=\"modal\">" . JText::_('JCANCEL') . "</button>";
		$footer .= "<button class=\"btn btn-primary delicious blue\" type=\"submit\" onclick=\"AKQuickAdd.submit('{$qid}', event);\">" . JText::_('JSUBMIT') . "</button>";

		$html .= ModalHelper::modalLink(JText::_($button_title), $qid, array('class' => $button_class, 'icon' => 'icon-new icon-white'));
		$html .= ModalHelper::renderModal($qid, $content, array('title' => JText::_($modal_title), 'footer' => $footer));

		return $html;
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
