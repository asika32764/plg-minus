<?php
/**
 * Part of Component {{extension.name.cap}} files.
 *
 * @copyright   Copyright (C) 2014 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Windwalker\Data\Data;

// No direct access
defined('_JEXEC') or die;

// Prepare script
JHtmlBehavior::multiselect('adminForm');

/**
 * Prepare data for this template.
 *
 * @var $container Windwalker\DI\Container
 * @var $data      Windwalker\Data\Data
 * @var $asset     Windwalker\Helper\AssetHelper
 * @var $grid      Windwalker\View\Helper\GridHelper
 * @var $date      \JDate
 */
$container = $this->getContainer();
$asset     = $container->get('helper.asset');
$grid      = $data->grid;
$date      = $container->get('date');

// Set order script.
$grid->registerTableSort();
?>

<!-- LIST TABLE -->
<table id="{{controller.item.name.lower}}List" class="table table-striped adminlist">

<!-- TABLE HEADER -->
<thead>
<tr>
	<!--SORT-->
	<th width="1%" class="nowrap center hidden-phone">
		<?php echo $grid->orderTitle(); ?>
	</th>

	<!--CHECKBOX-->
	<th width="1%" class="center">
		<?php echo JHtml::_('grid.checkAll'); ?>
	</th>

	<!--STATE-->
	<th width="5%" class="nowrap center">
		<?php echo $grid->sortTitle('JSTATUS', '{{controller.item.name.lower}}.state'); ?>
	</th>

	<!--TITLE-->
	<th class="center">
		<?php echo $grid->sortTitle('JGLOBAL_TITLE', '{{controller.item.name.lower}}.title'); ?>
	</th>

	<!--CATEGORY-->
	<th width="10%" class="center">
		<?php echo $grid->sortTitle('JCATEGORY', 'category.title'); ?>
	</th>

	<!--ACCESS VIEW LEVEL-->
	<th width="5%" class="center">
		<?php echo $grid->sortTitle('JGRID_HEADING_ACCESS', 'viewlevel.title'); ?>
	</th>

	<!--CREATED-->
	<th width="10%" class="center">
		<?php echo $grid->sortTitle('JDATE', '{{controller.item.name.lower}}.created'); ?>
	</th>

	<!--USER-->
	<th width="10%" class="center">
		<?php echo $grid->sortTitle('JAUTHOR', 'user.name'); ?>
	</th>

	<!--LANGUAGE-->
	<th width="5%" class="center">
		<?php echo $grid->sortTitle('JGRID_HEADING_LANGUAGE', 'lang.title'); ?>
	</th>

	<!--ID-->
	<th width="1%" class="nowrap center">
		<?php echo $grid->sortTitle('JGRID_HEADING_ID', '{{controller.item.name.lower}}.id'); ?>
	</th>
</tr>
</thead>

<!--PAGINATION-->
<tfoot>
<tr>
	<td colspan="15">
		<div class="pull-left">
			<?php echo $data->pagination->getListFooter(); ?>
		</div>
	</td>
</tr>
</tfoot>

<!-- TABLE BODY -->
<tbody>
<?php foreach ($data->items as $i => $item)
	:
	// Prepare data
	$item = new Data($item);

	// Prepare item for GridHelper
	$grid->setItem($item, $i);
	?>
	<tr class="{{controller.item.name.lower}}-row" sortable-group-id="<?php echo $item->catid; ?>">
		<!-- DRAG SORT -->
		<td class="order nowrap center hidden-phone">
			<?php echo $grid->dragSort(); ?>
		</td>

		<!--CHECKBOX-->
		<td class="center">
			<?php echo JHtml::_('grid.id', $i, $item->{{controller.item.name.lower}}_id); ?>
		</td>

		<!--STATE-->
		<td class="center">
			<div class="btn-group">
				<!-- STATE BUTTON -->
				<?php echo $grid->state() ?>

				<!-- CHANGE STATE DROP DOWN -->
				<?php echo $this->loadTemplate('dropdown'); ?>
			</div>
		</td>

		<!--TITLE-->
		<td class="n/owrap has-context quick-edit-wrap">
			<div class="item-title">
				<!-- Checkout -->
				<?php echo $grid->checkoutButton(); ?>

				<!-- Title -->
				<?php echo $grid->editTitle(); ?>
			</div>

			<!-- Sub Title -->
			<div class="small">
				<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
			</div>
		</td>

		<!--CATEGORY-->
		<td class="center">
			<?php echo $this->escape($item->category_title); ?>
		</td>

		<!--ACCESS VIEW LEVEL-->
		<td class="center">
			<?php echo $this->escape($item->viewlevel_title); ?>
		</td>

		<!--CREATED-->
		<td class="center">
			<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
		</td>

		<!--USER-->
		<td class="center">
			<?php echo $this->escape($item->user_name); ?>
		</td>

		<!--LANGUAGE-->
		<td class="center">
			<?php echo $grid->language(); ?>
		</td>

		<!--ID-->
		<td class="center">
			<?php echo $item->id; ?>
		</td>

	</tr>
<?php endforeach; ?>
</tbody>
</table>
