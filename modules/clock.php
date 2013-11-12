<?php
/**
*
* @package Board3 Portal v2.1
* @copyright (c) Board3 Group ( www.board3.de )
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace board3\portal\modules;

/**
* @package Clock
*/
class clock extends module_base
{
	/**
	* Allowed columns: Just sum up your options (Exp: left + right = 10)
	* top		1
	* left		2
	* center	4
	* right		8
	* bottom	16
	*/
	public $columns = 10;

	/**
	* Default modulename
	*/
	public $name = 'CLOCK';

	/**
	* Default module-image:
	* file must be in "{T_THEME_PATH}/images/portal/"
	*/
	public $image_src = 'portal_clock.png';

	/**
	* module-language file
	* file must be in "language/{$user->lang}/mods/portal/"
	*/
	public $language = 'portal_clock_module';

	/**
	* @inheritdoc
	*/
	public function get_allowed_columns()
	{
		return $this->columns;
	}

	/**
	* @inheritdoc
	*/
	public function get_name()
	{
		return $this->name;
	}

	/**
	* @inheritdoc
	*/
	public function get_image()
	{
		return $this->image_src;
	}

	/**
	* @inheritdoc
	*/
	public function get_language()
	{
		return $this->language;
	}

	/**
	* @inheritdoc
	*/
	public function get_template_side($module_id)
	{
		return 'clock_side.html';
	}

	/**
	* @inheritdoc
	*/
	public function get_template_acp($module_id)
	{
		return array(
			'title'	=> 'ACP_PORTAL_CLOCK_SETTINGS',
			'vars'	=> array(),
		);
	}
}
