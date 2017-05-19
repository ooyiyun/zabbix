<?php
/*
** Zabbix
** Copyright (C) 2001-2017 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

require_once dirname(__FILE__).'/../../include/blocks.inc.php';

class CControllerWidgetNavigationtreeItemEdit extends CController {

	protected function init() {
		$this->disableSIDValidation();
	}

	protected function checkInput() {
		$fields = [
			'map_name' => '',
			'map_id' => '',
			'mapid' => ''
		];

		$ret = $this->validateInput($fields);

		if (!$ret) {
			$this->setResponse(new CControllerResponseData(['main_block' => CJs::encodeJson('')]));
		}

		return $ret;
	}

	protected function checkPermissions() {
		return ($this->getUserType() >= USER_TYPE_ZABBIX_USER);
	}

	protected function doAction() {
		$title = null;
		$error = null;

		$map_item_name = $this->getInput('map_name', '');
		$map_mapid = $this->getInput('mapid', 0);
		$map_id = $this->getInput('map_id', 0);

		// build form
		$form = (new CForm('post'))
			->cleanItems()
			->setId('widget_dialogue_form')
			->setName('widget_dialogue_form');

		$formList = new CFormList();
		$formList->addRow(
			_('Name'),
			(new CTextBox('map.name.'.$map_id, $map_item_name))->setWidth(ZBX_TEXTAREA_MEDIUM_WIDTH)
		);

		$sysmap_id = 0;
		$sysmap_caption = '';

		if ($map_mapid) {
			$maps = API::Map()->get([
				'sysmapids' => [$map_mapid],
				'output' => API_OUTPUT_EXTEND
			]);

			if (($map = reset($maps)) !== false) {
				$sysmap_caption = $map['name'];
				$sysmap_id = $map['sysmapid'];
			}
		}

		$formList->addVar('linked_map_id', $sysmap_id);
		$formList->addRow(_('Linked map'), [
			(new CTextBox('caption', $sysmap_caption, true))->setWidth(ZBX_TEXTAREA_MEDIUM_WIDTH),
			(new CDiv())->addClass(ZBX_STYLE_FORM_INPUT_MARGIN),
			(new CButton('select', _('Select')))
				->addClass(ZBX_STYLE_BTN_GREY)
				->onClick('javascript: return PopUp("popup.php?srctbl=sysmaps&srcfld1=sysmapid&srcfld2=name'.
					'&dstfrm='.$form->getName().'&dstfld1=linked_map_id&dstfld2=caption");'
			)
		]);

		$form->addItem($formList);

		// prepare output
		$output = [
			'body' => $form->toString()
		];

		if (($messages = getMessages()) !== null) {
			$output['messages'] = $messages->toString();
		}

		if ($data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED) {
			CProfiler::getInstance()->stop();
			$output['debug'] = CProfiler::getInstance()->make()->toString();
		}

		echo (new CJson())->encode($output);
	}
}
