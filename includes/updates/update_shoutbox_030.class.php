<?php
/*	Project:	EQdkp-Plus
 *	Package:	Shoutbox Plugin
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2015 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('EQDKP_INC')){
	header('HTTP/1.0 404 Not Found');exit;
}


include_once(registry::get_const('root_path').'maintenance/includes/sql_update_task.class.php');

if (!class_exists('update_shoutbox_030')){
	class update_shoutbox_030 extends sql_update_task{

		public $author		= 'Aderyn';
		public $version		= '0.3.0';    // new version
		public $name		= 'Shoutbox 0.3.0 Update';
		public $type		= 'plugin_update';
		public $plugin_path	= 'shoutbox'; // important!

		/**
		* Constructor
		*/
		public function __construct(){
			parent::__construct();

			// init language
			$this->langs = array(
				'english' => array(
					'update_shoutbox_030'	=> 'Shoutbox 0.3.0 Update Package',
					'update_function'		=> 'Copy config',
					// SQL
					1						=> 'Delete guest setting',
					2						=> 'Delete location setting',
				),
				'german' => array(
					'update_shoutbox_030'	=> 'Shoutbox 0.3.0 Update Paket',
					'update_function'		=> 'Kopiere Einstellungen',
					// SQL
					1						=> 'Entferne Gast Einstellung',
					2						=> 'Entferne Positions Einstellung',
				),
			);

			// init SQL querys
			$this->sqls = array(
				1 => 'DELETE FROM `__config` WHERE `config_name`=\'sb_invisible_to_guests\';',
				2 => 'DELETE FROM `__config` WHERE `config_name`=\'sb_input_box_below\';',
			);
		}

		/**
		* update_function
		* Execute update function
		*
		* @returns  true/false
		*/
		public function update_function(){
			// default settings
			$new_settings = array();

			// copy all settings from shoutbox config table to core config
			$sql = 'SELECT config_name, config_value FROM `__shoutbox_config`;';
			$objQuery = $this->db->query($sql);

			if ($objQuery){
				while (($row = $objQuery->fetchAssoc())){
					$new_settings[$row['config_name']] = $row['config_value'];
				}
			}

			// insert settings into core config table
			$this->config->set($new_settings, '', 'shoutbox');

			// delete old config table
			$sql = 'DROP TABLE `__shoutbox_config`;';
			$this->db->query($sql);

			return true;
		}
	}
}
?>
