<?php
/**
 * Part of Component {{extension.name.cap}} files.
 *
 * @copyright   Copyright (C) 2014 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Windwalker\Data\Data;
use Windwalker\View\Layout\FileLayout;

JHtml::_('behavior.tooltip');

/**
 * Prepare data for this template.
 *
 * @var Windwalker\DI\Container       $container
 * @var Windwalker\Helper\AssetHelper $asset
 */
$container = $this->getContainer();
$input     = $container->get('input');
$grid      = $data->grid;
$data->asset = $container->get('helper.asset');

$function = $input->get('function', 'jSelectArticle');
?>

<div id="{{extension.name.lower}}" class="windwalker {{controller.list.name.lower}} tablelist row-fluid">
	<form action="<?php echo JURI::getInstance(); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

		<?php echo with(new FileLayout('joomla.searchtools.default'))->render(array('view' => $this->data)); ?>

		<table id="{{controller.item.name.lower}}List" class="adminlist table table-striped modal-list">
			<thead>
			<tr>
				<!--TITLE-->
				<th class="center">
					<?php echo $grid->sortTitle('JGLOBAL_TITLE', '{{controller.item.name.lower}}.title'); ?>
				</th>

				<!--CATEGORY-->
				<th class="center">
					<?php echo $grid->sortTitle('JCATEGORY', 'category.title'); ?>
				</th>

				<!--ACCESS VIEW LEVEL-->
				<th class="center">
					<?php echo $grid->sortTitle('JGRID_HEADING_ACCESS', 'viewlevel.title'); ?>
				</th>

				<!--CREATED-->
				<th class="center">
					<?php echo $grid->sortTitle('JDATE', '{{controller.item.name.lower}}.created'); ?>
				</th>

				<!--LANGUAGE-->
				<th class="center">
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
				<td colspan="10">
					<?php echo $data->pagination->getListFooter(); ?>
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
				<tr class="row<?php echo $i % 2; ?>">
					<!--TITLE-->
					<td class="n/owrap has-context quick-edit-wrap">
						<div class="item-title">
							<a class="pointer" style="cursor: pointer;"
								onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>('<?php echo $item->id; ?>','<?php echo $this->escape(addslashes($item->title)); ?>');"
								>
								<?php echo $this->escape($item->title); ?>
							</a>
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

					<!--LANGUAGE-->
					<td class="center">
						<?php
						if ($item->language == '*')
						{
							echo JText::alt('JALL', 'language');
						}
						else
						{
							echo $item->lang_title ? $this->escape($item->lang_title) : JText::_('JUNDEFINED');
						}
						?>
					</td>

					<!--ID-->
					<td class="center">
						<?php echo $item->id; ?>
					</td>

				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>