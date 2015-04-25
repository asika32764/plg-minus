<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Component
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Prototype admin model.
 *
 * @package     Windwalker.Framework
 * @subpackage  Component
 */
class AKModelAdmin extends JModelAdmin
{
	/**
	 * Component name.
	 *
	 * @var string
	 */
	protected $component = '';

	/**
	 * The URL view item variable.
	 *
	 * @var    string
	 */
	protected $item_name = '';

	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 */
	protected $list_name = '';

	/**
	 * Item cache.
	 *
	 * @var object
	 */
	protected $item = null;

	/**
	 * Category cache.
	 *
	 * @var object
	 */
	protected $category = null;

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param    type      The table type to instantiate
	 * @param    string    A prefix for the table class name. Optional.
	 * @param    array     Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 */
	public function getTable($type = null, $prefix = null, $config = array())
	{
		$prefix = $prefix ? $prefix : ucfirst($this->component) . 'Table';
		$type   = $type ? $type : $this->item_name;

		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param    array   $data     An optional array of data for the form to interogate.
	 * @param    boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm("{$this->option}.{$this->item_name}", $this->item_name, array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Get fields group. This Function is deprecated, use getFieldsGroup instead.
	 *
	 * @return      array   Fields groups.
	 * @deprecated  4.0
	 */
	public function getFields()
	{
		// Deprecation warning.
		JLog::add(__CLASS__ . '::' . __FUNCTION__ . '() is deprecated.', JLog::WARNING, 'deprecated');

		return $this->getFieldsName();
	}

	/**
	 * Get fields group.
	 *
	 * @return    array   Fields groups.
	 */
	public function getFieldsGroup()
	{
		if (!empty($this->fields_group))
		{
			return $this->fields_group;
		}

		$xml_file     = AKHelper::_('path.get', null, $this->option) . '/models/forms/' . $this->item_name . '.xml';
		$xml          = JFactory::getXML($xml_file);
		$fields       = $xml->xpath('/form/fields');
		$fields_name  = array();
		$fields_group = array();

		foreach ($fields as $field):
			if ((string) $field['name'] != 'other')
				$fields_name[] = (string) $field['name'];
			$fields_group[] = $field;
		endforeach;

		$this->fields_name = $fields_name;

		return $this->fields_group = $fields_group;
	}

	/**
	 * Get fields group name as array.
	 *
	 * @return  array   fields name array.
	 */
	public function getFieldsName()
	{
		if (!empty($this->fields_name))
		{
			return $this->fields_name;
		}

		$this->getFieldsGroup();

		return $this->fields_name;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState("{$this->option}.edit.{$this->item_name}.data", array());

		if (empty($data))
		{
			$data = $this->getItem();
		}
		else
		{
			$data = new JObject($data);

			// If Error occured and resend, just return data.
			return $data;
		}

		// Get params, convert $data->params['xxx'] to $data->param_xxx
		// ==========================================================================================
		if (isset($data->params) && is_array($data->params))
		{
			$data = AKHelper::_('array.pivotToPrefix', 'param_', $data->params, $data);
		}

		// This seeting is for Fields Group.
		// Convert data[field] to data[fields_group][field] then Jform can bind data into forms.
		// ==========================================================================================
		$fields = $this->getFields();
		$data   = AKHelper::_('array.pivotToTwoDimension', $data, $fields);

		// If page reload, retain data
		// ==========================================================================================
		$retain = JRequest::getVar('retain', 0);

		// Set Change Field Type Retain Data
		if ($retain)
		{
			$data = JRequest::getVar('jform');
		}

		return $data;
	}

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState()
	{
		parent::populateState();

		// Set Nested Item
		$table = $this->getTable();

		if ($table instanceof JTableNested)
		{
			$nested = true;
		}
		else
		{
			$nested = false;
		}

		$this->setState('item.nested', $nested);
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm  $form  A JForm object.
	 * @param   mixed  $data  The data expected for the form.
	 * @param   string $group The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 * @see     JFormField
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param    integer    The id of the primary key.
	 *
	 * @return   mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		$key = $this->getTable()->getKeyName();

		if ($item->$key === null)
		{
			return false;
		}

		return $this->item = $item;
	}

	/**
	 * Method to get category by catid.
	 *
	 * @param   integer $pk Category id.
	 *
	 * @return  mixed   Category object or false.
	 */
	public function getCategory($pk = null)
	{
		if (!empty($this->category))
		{
			return $this->category;
		}

		$pk = $pk ? $pk : $this->getItem()->catid;

		$this->category = JTable::getInstance('Category');

		if (!$this->category->load($pk))
		{
			$this->setError($this->category->getError());

			return false;
		}

		return $this->category;
	}

	/**
	 * Get an related item of current item.
	 *
	 * @param   string $name      The table name.
	 * @param   mixed  $condition May be id or a condition array for JTable to fetch item.
	 *
	 * @return  JObject Related ttem.
	 */
	public function getRelatedItem($name, $condition)
	{
		$table = $this->getTable(ucfirst($name));

		if ($table->load($condition))
		{
			$item = get_object_vars($table);

			foreach ($item as $key => $val):
				if ($key == '_errors') continue;

				$item[strtolower($name) . '_' . $key] = $val;
				unset($item[$key]);
			endforeach;

			$item = new JObject($item);

			return $item;
		}
		else
		{
			//$this->setError($table->getError());
			return false;
		}
	}

	/**
	 * Get related items of current item.
	 *
	 * @param   string $name      The model name.
	 * @param   array  $condition A condition array for ModelList Filter State.
	 *
	 * @return array Related items.
	 */
	public function getRelatedItems($name, $condition = array())
	{
		$model = JModelLegacy::getInstance(ucfirst($name), ucfirst($this->component) . 'Model', array('ignore_request' => true));

		$model->setState('filter', $condition);
		$items = $model->getItems();

		return (array) $items;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   object    A record object.
	 *
	 * @return  array  An array of conditions to add to add to ordering queries.
	 */
	protected function getReorderConditions($table)
	{
		$condition = array();

		if (property_exists($table, 'catid'))
		{
			$condition[] = 'catid = ' . $table->catid;
		}

		return $condition;
	}

	/**
	 * Method rebuild the entire nested set tree.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 */
	public function rebuild()
	{
		// Set parent not 0
		$db = JFactory::getDbo();
		$q  = $db->getQuery(true);

		$q->update('#__' . $this->component . '_' . $this->list_name)
			->set('parent_id=1')
			->set('level=1')
			->set('rgt=0')
			->set('lft=0')
			->where('id != 1')
			->where('parent_id = 0');

		$db->setQuery($q);
		$db->execute();

		// Get an instance of the table object.
		$table = $this->getTable();

		if (!$table->rebuild())
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to save the reordered nested set tree.
	 * First we save the new order values in the lft values of the changed ids.
	 * Then we invoke the table rebuild to implement the new ordering.
	 *
	 * @param   array   $idArray   An array of primary key ids.
	 * @param   integer $lft_array The lft value
	 *
	 * @return  boolean  False on failure or error, True otherwise
	 */
	public function saveorderNested($idArray = null, $lft_array = null)
	{
		// Get an instance of the table object.
		$table = $this->getTable();

		if (!$table->saveorder($idArray, $lft_array))
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		$date = JFactory::getDate('now', JFactory::getConfig()->get('offset'));
		$user = JFactory::getUser();
		$db   = JFactory::getDbo();
		$task = JRequest::getVar('task');

		// Handle Save as copy
		if ($task == 'save2copy')
		{
			if (property_exists($table, 'id'))
			{
				$table->id = null;
			}

			if (property_exists($table, 'title'))
			{
				$table->title = $table->title . ' (2)';
			}

			if (property_exists($table, 'alias'))
			{
				$table->alias = $table->alias . ' 2';
			}
		}

		// Alias
		if (property_exists($table, 'alias'))
		{
			if (!$table->alias)
			{
				$table->alias = JFilterOutput::stringURLSafe(trim($table->title));
			}
			else
			{
				$table->alias = JFilterOutput::stringURLSafe(trim($table->alias));
			}

			if (!$table->alias)
			{
				$table->alias = JFilterOutput::stringURLSafe($date->toSql(true));
			}
		}

		// created date
		if (property_exists($table, 'created') && !$table->created)
		{
			$table->created = $date->toSql(true);
		}

		// publish_up date
		if (property_exists($table, 'publish_up') && !$table->publish_up)
		{
			$table->publish_up = $date->toSql(true);
		}

		// modified date
		if (property_exists($table, 'modified') && $table->id)
		{
			$table->modified = $date->toSql(true);
		}

		// created user
		if (property_exists($table, 'created_by') && !$table->created_by)
		{
			$table->created_by = $user->get('id');
		}

		// modified user
		if (property_exists($table, 'modified_by') && $table->id)
		{
			$table->modified_by = $user->get('id');
		}

		// Version
		if (isset($table->version))
		{
			$table->version++;
		}

		// Set Ordering or Nested ordering

		// set catid = parent->catid
		if ($table instanceof JTableNested)
		{
			$table->parent_id = $table->parent_id ? $table->parent_id : 1;

			$old = $this->getTable();
			$old->load($table->id);

			if ($table->parent_id != $old->get('parent_id'))
			{
				$parent = $this->getTable();
				$parent->load($table->parent_id);
				$table->catid = $parent->catid;

				$table->setLocation($table->parent_id, 'last-child');

				// Rebuild the path for the category:
				if (!$table->rebuildPath())
				{
					$this->setError($table->getError());

					return false;
				}
			}

		}
		elseif (property_exists($table, 'ordering'))
		{
			if (empty($table->id))
			{
				$this->setOrderPosition($table);
			}
		}

		// Set Fields if CCKEngine Enabled
		if ($this->getState('CCKEngine.enabled'))
		{
			AKHelper::_('fields.setFieldTable', $table, null, array('context' => "{$this->option}.{$this->item_name}"));
		}
	}

	/**
	 * Method to set new item ordering as first or last.
	 *
	 * @param   JTable $table    Item table to save.
	 * @param   string $position 'first' or other are last.
	 *
	 * @return  type
	 */
	public function setOrderPosition($table, $position = null)
	{
		if ($position == 'first')
		{
			if (!$table->ordering)
			{
				$table->reorder('catid = ' . (int) $table->catid . ' AND published >= 0');
			}
		}
		else
		{
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__' . $this->component . '_' . $this->list_name);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * If category need authorize, we can write in this method.
	 *
	 * @param   int $record category record.
	 *
	 * @return  boolean Can edit or not.
	 */
	public function canCategoryCreate($record)
	{
		return true;
	}

	/**
	 * Method to duplicate items.
	 *
	 * @param   array &$pks An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		// Initialise variables.
		$user = JFactory::getUser();
		$db   = $this->getDbo();

		// Access checks.
		if (!$user->authorise('core.create', 'com_' . $this->component))
		{
			throw new Exception(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($table->load($pk, true))
			{
				// Reset the id to create a new record.
				$table->id = 0;

				// Alter the title.
				if (property_exists($table, 'title'))
				{
					$table->title = $allow_fields['title'] = JString::increment($table->title);
				}

				if (property_exists($table, 'alias'))
				{
					$table->alias = $allow_fields['alias'] = JString::increment($table->alias, 'dash');
				}

				// Unpublish duplicate item
				// $table->published = 0;

				if (!$table->check() || !$table->store())
				{
					throw new Exception($table->getError());
				}

			}
			else
			{
				throw new Exception($table->getError());
			}
		}

		// Clear modules cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   object $form  The form to validate against.
	 * @param   array  $data  The data to validate.
	 * @param   string $group The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   11.1
	 */
	public function validate($form, $data, $group = null)
	{
		if ($result = parent::validate($form, $data, $group))
		{
			// for Fields group
			// Convert jform[fields_group][field] to jform[field] or JTable cannot bind data.
			// ==========================================================================================
			$result = AKHelper::_('array.pivotFromTwoDimension', $result);
		}

		return $result;
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @param   array $commands An array of commands to perform.
	 * @param   array $pks      An array of item ids.
	 * @param   array $contexts An array of item contexts.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 */
	public function batch($commands, $pks, $contexts)
	{
		$table     = $this->getTable();
		$user      = JFactory::getUser();
		$nested    = ($table instanceof JTableNested) ? true : false;
		$extension = $this->option;
		$i         = 0;

		// Sanitize user ids.
		$pks = array_unique($pks);
		JArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks))
		{
			$this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));

			return false;
		}

		$done = array();

		$cmd = JArrayHelper::getValue($commands, 'move_copy', 'm');
		unset($commands['move_copy']);

		// unset no value keys
		foreach ($commands as $key => $val){
			if ($val == '')
			{
				unset($commands[$key]);
				continue;
			}
		}

		// Nested Batch
		// ==========================================================================================
		$parentId      = JArrayHelper::getValue($commands, 'parent_id');
		$nested_copied = false;

		if ($nested && $parentId)
		{
			if ($cmd == 'c')
			{
				$result = $this->batchCopyNested($parentId, $pks, $contexts);

				if (is_array($result))
				{
					$pks = $result;
				}
				else
				{
					return false;
				}
			}
			elseif ($cmd == 'm' && !$this->batchMoveNested($parentId, $pks, $contexts))
			{
				return false;
			}

			$done          = true;
			$nested_copied = true;
		}

		// Start Batch Process
		// ==========================================================================================
		foreach ($pks as $pk):

			$hasAction = false;

			$table->load($pk);
			$allow_fields = array();

			// Can item editable?
			if (!property_exists($table, 'asset_id'))
			{
				$contexts[$pk] = explode('.', $contexts[$pk]);
				$contexts[$pk] = $contexts[$pk][0];
			}

			if (!$user->authorise('core.edit', $contexts[$pk]))
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}

			// Set Value
			foreach ($commands as $key => $val)
			{
				if ($val == '')
				{
					unset($commands[$key]);
					continue;
				}

				// Detect Category Access
				if ($key == 'catid')
				{
					if (!$this->canCategoryCreate($val))
					{
						$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));

						return false;
					}
				}

				// Set value in table
				if (property_exists($table, $key))
				{
					$table->$key        = $val;
					$allow_fields[$key] = $val;
				}

				$done      = true;
				$hasAction = true;
			}

			// If no action has to execute, continue;
			if (!$hasAction)
			{
				continue;
			}

			// Handle Nested Batch
			// ==========================================================================================
			if ($nested && in_array('parent_id', $commands))
			{
				// If assit_id dosen't exists, don't check item access.
				$canCreate = ($commands['parent_id'] == $table->getRootId()) || !property_exists($table, 'asset_id')
					? $user->authorise('core.create', $extension)
					: $user->authorise('core.create', $extension . '.' . $this->item_name . '.' . $commands['parent_id']);

				if (!$canCreate)
				{
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));

					return false;
				}
			}

			// Copy or Move
			// ==========================================================================================
			if ($cmd == 'c' && !$nested_copied)
			{
				// Set id as New
				$table->id = null;

				// Handle Title increment
				$table2 = $this->getTable();

				if (property_exists($table, 'title')) $allow_fields['title'] = $table->title;
				if (property_exists($table, 'alias')) $allow_fields['alias'] = $table->alias;

				// Get item with same name & alias, if true, increment title & alias
				if ($table2->load($allow_fields))
				{
					if (property_exists($table, 'title'))
					{
						$table->title = $allow_fields['title'] = JString::increment($table->title);
					}

					if (property_exists($table, 'alias'))
					{
						$table->alias = $allow_fields['alias'] = JString::increment($table->alias, 'dash');
					}
				}
			}

			// Check the row.
			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			// Store the row.
			if (!$table->store())
			{
				$this->setError($table->getError());

				return false;
			}

		endforeach;

		// Clean the cache
		$this->cleanCache();

		if (!$done)
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch copy items to be another item's children.
	 *
	 * @param   integer $value    The new item.
	 * @param   array   $pks      An array of row IDs.
	 * @param   array   $contexts An array of item contexts.
	 *
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 */
	protected function batchCopyNested($value, $pks, $contexts)
	{
		$parentId  = $value ? (int) $value : 1;
		$table     = $this->getTable();
		$db        = $this->getDbo();
		$user      = JFactory::getUser();
		$extension = $this->option;
		$i         = 0;

		// Check that the parent exists
		if ($parentId)
		{
			if (!$table->load($parentId))
			{
				if ($error = $table->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					// Non-fatal error
					$this->setError(JText::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
					$parentId = 0;
				}
			}
			// Check that user has create permission for parent category
			$canCreate = ($parentId == $table->getRootId()) || !property_exists($table, 'asset_id')
				? $user->authorise('core.create', $extension)
				: $user->authorise('core.create', $extension . '.' . $this->item_name . '.' . $parentId);

			if (!$canCreate)
			{
				// Error since user cannot create in parent category
				$this->setError(JText::_($this->text_prefix . '_BATCH_CANNOT_CREATE'));

				return false;
			}
		}

		// If the parent is 0, set it to the ID of the root item in the tree
		if (empty($parentId))
		{
			if (!$parentId = $table->getRootId())
			{
				$this->setError($db->getErrorMsg());

				return false;
			}
			// Make sure we can create in root
			elseif (!$user->authorise('core.create', $extension))
			{
				$this->setError(JText::_($this->text_prefix . '_BATCH_CANNOT_CREATE'));

				return false;
			}
		}

		// We need to log the parent ID
		$parents = array();

		// Calculate the emergency stop count as a precaution against a runaway loop bug
		$query = $db->getQuery(true);
		$query->select('COUNT(id)');
		$query->from($db->quoteName("#__{$this->component}_{$this->list_name}"));
		$db->setQuery($query);

		try
		{
			$count = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Parent exists so we let's proceed
		while (!empty($pks) && $count > 0)
		{
			// Pop the first id off the stack
			$pk = array_shift($pks);

			$table->reset();

			// Check that the row actually exists
			if (!$table->load($pk))
			{
				if ($error = $table->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Copy is a bit tricky, because we also need to copy the children
			$query->clear();
			$query->select('id');
			$query->from($db->quoteName("#__{$this->component}_{$this->list_name}"));
			$query->where('lft > ' . (int) $table->lft);
			$query->where('rgt < ' . (int) $table->rgt);
			$db->setQuery($query);
			$childIds = $db->loadColumn();

			// Add child ID's to the array only if they aren't already there.
			foreach ($childIds as $childId)
			{
				if (!in_array($childId, $pks))
				{
					array_push($pks, $childId);
				}
			}

			// Make a copy of the old ID and Parent ID
			$oldId       = $table->id;
			$oldParentId = $table->parent_id;

			// Reset the id because we are making a copy.
			$table->id = 0;

			// If we a copying children, the Old ID will turn up in the parents list
			// otherwise it's a new top level item
			$table->parent_id = isset($parents[$oldParentId]) ? $parents[$oldParentId] : $parentId;

			// Set the new location in the tree for the node.
			$table->setLocation($table->parent_id, 'last-child');

			// TODO: Deal with ordering?
			//$table->ordering    = 1;
			$table->level    = null;
			$table->asset_id = null;
			$table->lft      = null;
			$table->rgt      = null;

			// Alter the title & alias
			list($title, $alias) = $this->generateNewTitle($table->parent_id, $table->alias, $table->title);
			$table->title = $title;
			$table->alias = $alias;

			// Store the row.
			if (!$table->store())
			{
				$this->setError($table->getError());

				return false;
			}

			// Get the new item ID
			$newId = $table->get('id');

			// Add the new ID to the array
			$newIds[$i] = $newId;
			$i++;

			// Now we log the old 'parent' to the new 'parent'
			$parents[$oldId] = $table->id;
			$count--;
		}

		// Rebuild the hierarchy.
		if (!$table->rebuild())
		{
			$this->setError($table->getError());

			return false;
		}

		// Rebuild the tree path.
		if (!$table->rebuildPath($table->id))
		{
			$this->setError($table->getError());

			return false;
		}

		return $newIds;
	}

	/**
	 * Batch move items to be another item's children.
	 *
	 * @param   integer $value    The new item ID.
	 * @param   array   $pks      An array of row IDs.
	 * @param   array   $contexts An array of item contexts.
	 *
	 * @return  boolean  True on success.
	 */
	protected function batchMoveNested($value, $pks, $contexts)
	{
		$parentId  = $value;
		$table     = $this->getTable();
		$db        = $this->getDbo();
		$query     = $db->getQuery(true);
		$user      = JFactory::getUser();
		$extension = $this->option;

		// Check that the parent exists.
		if ($parentId)
		{
			if (!$table->load($parentId))
			{
				if ($error = $table->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					// Non-fatal error
					$this->setError(JText::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
					$parentId = 0;
				}
			}

			// Check that user has create permission for parent category
			$canCreate = ($parentId == $table->getRootId()) || !property_exists($table, 'asset_id')
				? $user->authorise('core.create', $extension)
				: $user->authorise('core.create', $extension . '.' . $this->item_name . '.' . $parentId);

			if (!$canCreate)
			{
				// Error since user cannot create in parent category
				$this->setError(JText::_($this->text_prefix . '_BATCH_CANNOT_CREATE'));

				return false;
			}

			// Check that user has edit permission for every category being moved
			// Note that the entire batch operation fails if any category lacks edit permission
			foreach ($pks as $pk)
			{
				$canEdit = !property_exists($table, 'asset_id')
					? $user->authorise('core.create', $extension)
					: $user->authorise('core.create', $extension . '.' . $this->item_name . '.' . $pk);

				if (!$canEdit)
				{
					// Error since user cannot edit this category
					$this->setError(JText::_($this->text_prefix . '_BATCH_CANNOT_EDIT'));

					return false;
				}
			}
		}

		// We are going to store all the children and just move the category
		$children = array();

		// Parent exists so we let's proceed
		foreach ($pks as $pk)
		{
			// Check that the row actually exists
			if (!$table->load($pk))
			{
				if ($error = $table->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Set the new location in the tree for the node.
			$table->setLocation($parentId, 'last-child');

			// Check if we are moving to a different parent
			if ($parentId != $table->parent_id)
			{
				// Add the child node ids to the children array.
				$query->clear();
				$query->select('id');
				$query->from($db->quoteName("#__{$this->component}_{$this->list_name}"));
				$query->where($db->quoteName('lft') . ' BETWEEN ' . (int) $table->lft . ' AND ' . (int) $table->rgt);
				$db->setQuery($query);

				try
				{
					$children = array_merge($children, (array) $db->loadColumn());
				} catch (RuntimeException $e)
				{
					$this->setError($e->getMessage());

					return false;
				}
			}

			// Store the row.
			if (!$table->store())
			{
				$this->setError($table->getError());

				return false;
			}

			// Rebuild the tree path.
			if (!$table->rebuildPath())
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Process the child rows
		if (!empty($children))
		{
			// Remove any duplicates and sanitize ids.
			$children = array_unique($children);
			JArrayHelper::toInteger($children);
		}

		return true;
	}
}
