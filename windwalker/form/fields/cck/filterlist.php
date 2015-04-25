<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Form.CCK
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

JForm::addFieldPath(AKPATH_FORM . '/fields');
JFormHelper::loadFieldType('List');

/**
 * Supports an HTML select list of Filter.
 *
 * @package     Windwalker.Framework
 * @subpackage  Form.CCK
 */
class JFormFieldFilterlist extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'Filterlist';

	public $value;

	public $name;

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	public function getOptions()
	{

		if ($this->value)
		{
			$this->value = (string) $this->element['default'];
		}

		$element = $this->element;

		$types = array(
			'raw',
			'int',
			'uint',
			'float',
			'bool',
			'word',
			'alnum',
			'base64',
			'string',
			'safehtml',
			'array',
			'url',
			'path',
			'username',
			'tel'
		);

		// Includes
		$includes = $element['include'];

		if ($includes)
		{
			$includes = explode(',', $includes);
			foreach ($includes as &$include):
				$include = trim($include);
			endforeach;

			$types = $includes;
		}

		// Excludes
		$excludes = (string) $element['exclude'];

		if ($excludes)
		{
			$excludes = explode(',', $excludes);
			foreach ($excludes as &$exclude):
				$exclude = trim($exclude);
			endforeach;
		}
		else
		{
			$excludes = array();
		}

		// Set Options
		$options = array();

		foreach ($types as $type):
			$type = str_replace('.xml', '', $type);

			// Excludes
			if (in_array($type, $excludes))
			{
				continue;
			}

			$options[] = JHtml::_(
				'select.option', (string) $type,
				JText::_('LIB_WINDWALKER_FILTERLIST_' . strtoupper($type))
			);
		endforeach;

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
