<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// No direct access
use Windwalker\DI\Container;
use Windwalker\Helper\XmlHelper;

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('text');

include_once JPATH_LIBRARIES . '/windwalker/src/init.php';

/**
 * Supports a File finder to pick files.
 *
 * @since 2.0
 */
class JFormFieldFinder extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Finder';

	/**
	 * Show as tooltip.
	 *
	 * @var boolean
	 */
	protected $showAsTooltip = false;

	/**
	 * The initialised state of the document object.
	 *
	 * @var boolean
	 */
	protected static $initialised = false;

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	public function getInput()
	{
		// Load the modal behavior script.
		JHtmlBehavior::modal('a.modal');

		if (!self::$initialised)
		{
			$this->setScript();
		}

		// Setup variables for display.
		// ================================================================
		$html     = array();
		$disabled = XmlHelper::getBool($this->element, 'disabled');
		$readonly = XmlHelper::getBool($this->element, 'readonly');
		$link     = $this->getLink();
		$title    = $this->getTitle();

		// Set Title
		// ================================================================
		if (empty($title))
		{
			$title = \JText::_(XmlHelper::get($this->element, 'select_label', 'LIB_WINDWALKER_FORMFIELD_FINDER_SELECT_FILE'));
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The text field.
		// ================================================================
		$preview = $this->getPreview();

		// The current user display field.
		$html[] = '<span class="' . (!$disabled && !$readonly ? 'input-append' : '') . '">';
		$html[] = '<input type="text" class="' . (!$disabled && !$readonly ? 'input-medium ' . $this->element['class'] : $this->element['class']) . '" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';

		if (!$disabled && !$readonly) :
			$html[] = '<a class="modal btn btn-primary" title="' . JText::_('LIB_WINDWALKER_FORMFIELD_FINDER_BROWSE_FILES') . '"  href="' . $link . '&amp;' . JSession::getFormToken() . '=1" rel="{handler: \'iframe\', size: {x: 920, y: 450}}">
							<i class="icon-picture"></i> ' . JText::_('LIB_WINDWALKER_FORMFIELD_FINDER_BROWSE_FILES')
				. '</a>';
		endif;
		$html[] = '</span>';

		// The  class='required' for client side validation
		// ================================================================
		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		// Velue store input
		$disabled_attr = $disabled ? ' disabled="true" ' : '';
		$html[]        = '<input type="hidden" id="' . $this->id . '"' . $class . ' name="' . $this->name . '" value="' . $this->value . '" ' . $disabled_attr . ' />';

		$html = implode("\n", $html);

		// Tooltip Preview
		// ================================================================
		if ($this->showAsTooltip)
		{
			$html = $preview . $html;
			$html = '<div class="input-prepend input-append" style="margin-right: 7px;">' . $html . '</div>';
		}

		// Clear Button
		// ================================================================
		$clear_title = JText::_('LIB_WINDWALKER_FORMFIELD_FINDER_SELECT_FILE');

		if (!$disabled && !$readonly) :
			$html .= '<a class="btn btn-danger delicious light red fltlft hasTooltip" title="' . JText::_('JLIB_FORM_BUTTON_CLEAR') . '"' . ' href="#" onclick="';
			$html .= "AKFinderClear('{$this->id}', '{$clear_title}');";
			$html .= 'return false;';
			$html .= '">';
			$html .= '<i class="icon-remove"></i></a>';
		endif;

		// Image Preview
		// ================================================================
		if (!$this->showAsTooltip)
		{
			$html = $html . $preview;
		}

		return $html;
	}

	/**
	 * Get Preview Image.
	 *
	 * @return  string Preview image html.
	 */
	public function getPreview()
	{
		// The Preview.
		$preview       = (string) $this->element['preview'];
		$showPreview   = true;
		$showAsTooltip = false;

		switch ($preview)
		{
			case 'no': // Deprecated parameter value
			case 'false':
			case 'none':
				$showPreview = false;
				break;

			case 'yes': // Deprecated parameter value
			case 'true':
			case 'show':
				break;

			case 'tooltip':
			default:
				$this->showAsTooltip = $showAsTooltip = true;
				$options = array(
					'onShow' => 'AKFinderRefreshPreviewTip(this)',
				);

				JHtmlBehavior::tooltip('.hasTipPreview', $options);
				break;
		}

		if ($showPreview)
		{
			if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value))
			{
				$src = JURI::root() . $this->value;
			}
			else
			{
				$src = '';
			}

			$width  = (int) XmlHelper::get($this->element, 'preview_width', 300);
			$height = (int) XmlHelper::get($this->element, 'preview_height', 200);
			$style  = '';
			$style .= ($width > 0)  ? 'max-width:' . $width . 'px;'   : '';
			$style .= ($height > 0) ? 'max-height:' . $height . 'px;' : '';
			$style .= !$showAsTooltip ? 'margin: 10px 0;' : '';

			$imgattr = array(
				'id'    => $this->id . '_preview',
				'class' => 'media-preview',
				'style' => $style,
			);

			$imgattr['class'] = $showAsTooltip ? $imgattr['class'] : $imgattr['class'] . ' img-polaroid';

			$img             = JHtml::image($src, JText::_('JLIB_FORM_MEDIA_PREVIEW_ALT'), $imgattr);
			$previewImg      = '<div id="' . $this->id . '_preview_img"' . ($src ? '' : ' style="display:none"') . '>' . $img . '</div>';
			$previewImgEmpty = '<div id="' . $this->id . '_preview_empty"' . ($src ? ' style="display:none"' : '') . '>'
				. JText::_('JLIB_FORM_MEDIA_PREVIEW_EMPTY') . '</div>';

			$html[] = '<div class="media-preview add-on fltlft">';

			if ($showAsTooltip)
			{
				$tooltip = $previewImgEmpty . $previewImg;
				$options = array(
					'title' => JText::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE'),
					'text'  => '<i class="icon-eye"></i>',
					'class' => 'hasTipPreview'
				);

				$options['text'] = JVERSION >= 3 ? $options['text'] : JText::_('JLIB_FORM_MEDIA_PREVIEW_TIP_TITLE');
				$html[]          = JHtml::tooltip($tooltip, $options);
			}
			else
			{
				$html[] = ' ' . $previewImgEmpty;
				$html[] = ' ' . $previewImg;
				$html[] = '<script type="text/javascript">AKFinderRefreshPreview("' . $this->id . '");</script>';
			}

			$html[] = '</div>';
		}

		return implode("\n", $html);
	}

	/**
	 * Set Selecting JS.
	 *
	 * @return void
	 */
	public function setScript()
	{
		// Build Select script.
		$url_root = JURI::root();

		$script = <<<SCRIPT
        // Do Select
        var AKFinderSelect = function(id, selected, elFinder, root){
            if(selected.length < 1) return ;
            var link    = elFinder.url(selected[0].hash) ;
            var name    = selected[0].name ;
            
            // Clean DS
            link = link.replace(/\\\\/g, '/');
            
            link = link.replace( root, '' );
            
            // Detect is image
            var onlyImage = false ;
            
            if( selected[0].mime.substring(0, 5) == 'image' ) {
                $(id).set('image', 1);
                $(id).set('mime', selected[0].mime.split('/')[0]);
                
                document.id(id).value = link;
                document.id(id+"_name").value = name;
            }else{
                $(id).set('image', 0);
                $(id).set('mime', selected[0].mime.split('/')[1]);
                
                if(!onlyImage) {
                    document.id(id).value = link;
                    document.id(id+"_name").value = name;
                }else{
                    //SqueezeBox.close();
                    return ;
                }
            }
            
            AKFinderRefreshPreview(id);
            setTimeout( function(){
                SqueezeBox.close();
            } ,200);
        }
        
        // Clear Select
        var AKFinderClear = function(id, title){
            document.id(id).value = '';
            document.id(id+"_name").value = title;
            
            AKFinderRefreshPreview(id);
        };

        // Refresh Preview
        var AKFinderRefreshPreview = function(id) {
            var value = document.id(id).value;
            var input = $(id) ;
            var img = document.id(id + "_preview");
            var img_ext = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'];
            var ext = value.split('.').getLast();
            if (img) {
                if ( img_ext.contains(ext.toLowerCase()) ) {
                    img.src = "{$url_root}" + value;
                    document.id(id + "_preview_empty").setStyle("display", "none");
                    document.id(id + "_preview_img").setStyle("display", "");
                } else {
                    img.src = ""
                    document.id(id + "_preview_empty").setStyle("display", "none");
                    document.id(id + "_preview_img").setStyle("display", "none");
                } 
            }
            
            if(!value){
                img.src = ""
                document.id(id + "_preview_empty").setStyle("display", "");
                document.id(id + "_preview_img").setStyle("display", "none");
            }
        }
        
        // Refresh Preview for Tips
        var AKFinderRefreshPreviewTip = function(tip)
        {
            var img = tip.getElement("img.media-preview");
            tip.getElement("div.tip").setStyle("max-width", "none");
            var id = img.getProperty("id");
            id = id.substring(0, id.length - "_preview".length);
            AKFinderRefreshPreview(id);
            tip.setStyle("display", "block");
        }
SCRIPT;

		// Add the script to the document head.
		$asset = Container::getInstance()->get('helper.asset');
		$asset->internalJS($script);
	}

	/**
	 * Get item title.
	 *
	 * @return string The title text.
	 */
	public function getTitle()
	{
		$path = $this->value;

		if (!$path)
		{
			return null;
		}

		$path = JPath::clean($path, '/');
		$path = explode('/', $path);

		$file_name = array_pop($path);

		return $file_name;
	}

	/**
	 * Get Finder link.
	 *
	 * @return string The link string.
	 */
	public function getLink()
	{
		$input   = Container::getInstance()->get('input');
		$handler = $this->element['handler'] ? (string) $this->element['handler'] : $input->get('option');

		$root       = XmlHelper::get($this->element, 'root', '/');
		$start_path = XmlHelper::get($this->element, 'start_path', '/');
		$onlymimes  = XmlHelper::get($this->element, 'onlymimes', '');

		$link = "index.php?option={$handler}&task=finder.elfinder.display&tmpl=component&finder_id={$this->id}&root={$root}&start_path={$start_path}&onlymimes={$onlymimes}";

		return $link;
	}
}
