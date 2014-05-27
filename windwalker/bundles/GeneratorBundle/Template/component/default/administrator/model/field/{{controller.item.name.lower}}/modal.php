<?php
/**
 * Part of Component {{extension.name.cap}} files.
 *
 * @copyright   Copyright (C) 2014 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

include_once JPATH_LIBRARIES . '/windwalker/src/init.php';
JForm::addFieldPath(WINDWALKER_SOURCE . '/Form/Fields');
JFormHelper::loadFieldClass('Modal');

/**
 * Supports a modal picker.
 */
class JFormField{{controller.item.name.cap}}_Modal extends JFormFieldModal
{
	/**
	 * The form field type.
	 *
	 * @var string
	 * @since    1.6
	 */
	protected $type = '{{controller.item.name.cap}}_Modal';

	/**
	 * List name.
	 *
	 * @var string
	 */
	protected $view_list = '{{controller.list.name.lower}}';

	/**
	 * Item name.
	 *
	 * @var string
	 */
	protected $view_item = '{{controller.item.name.lower}}';

	/**
	 * Extension name, eg: com_content.
	 *
	 * @var string
	 */
	protected $extension = '{{extension.element.lower}}';

}
