<?php

/*
Copyright Franck Theeten, 2025

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 3.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see https://www.gnu.org/licenses/.

*/

namespace ReferentialIntegrity\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;


class ConfigForm extends Form
{
	
	public function init()
	{
		$this->add(
		[
			'name' => 'submit',
			'type' => Element\Submit::class,
			'attributes'=> [
				'value' => 'Save',
				'class' => 'button'
			],
		]
		);
	}
	
}


?>