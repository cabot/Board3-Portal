<?php
/**
* @package Portal - Attachments
* @version $Id$
* @copyright (c) 2009, 2010 Board3 Portal Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package Modulname
*/
class portal_attachments_module
{
	/**
	* Allowed columns: Just sum up your options (Exp: left + right = 10)
	* top		1
	* left		2
	* center	4
	* right		8
	* bottom	16
	*/
	var $columns = 31;

	/**
	* Default modulename
	*/
	var $name = 'PORTAL_ATTACHMENTS';

	/**
	* Default module-image:
	* file must be in "{T_THEME_PATH}/images/portal/"
	*/
	var $image_src = 'portal_attach.png';

	/**
	* module-language file
	* file must be in "language/{$user->lang}/mods/portal/"
	*/
	var $language = 'portal_attachments_module';

	function get_template_center($module_id)
	{
		global $config, $template, $db, $user, $auth, $phpEx, $phpbb_root_path;

		$attach_forums = false;
		$where = '';
		$filetypes = array();

		// Get filetypes and put them into an array
		if(isset($config['board3_attachments_filetype']) && strlen($config['board3_attachments_filetype']) > 0)
		{
			$filetypes = explode(',', $config['board3_attachments_filetype']);
		}

		if($config['board3_attachments_forum_ids'] !== '')
		{
			$attach_forums_config = (strpos($config['board3_attachments_forum_ids'], ',') !== false) ? explode(',', $config['board3_attachments_forum_ids']) : array($config['board3_attachments_forum_ids']);
			$forum_list =  array_unique(array_keys($auth->acl_getf('f_read', true)));
			
			if($config['board3_attachments_forum_exclude'])
			{
				$forum_list = array_unique(array_diff($forum_list, $attach_forums_config));
			}
			else
			{
				$forum_list =  array_unique(array_intersect($attach_forums_config, $forum_list));
			}
		}
		else
		{
			$forum_list =  array_unique(array_keys($auth->acl_getf('f_read', true)));
		}

		if(sizeof($forum_list))
		{
			$attach_forums = true;
			$where = 'AND ' . $db->sql_in_set('t.forum_id', $forum_list);
		}

		if(sizeof($filetypes))
		{
			if($config['board3_attachments_exclude'])
			{
				$where .= ' AND ' . $db->sql_in_set('a.extension', $filetypes, true);
			}
			else
			{
				$where .= ' AND ' . $db->sql_in_set('a.extension', $filetypes);
			}
		}

		if($attach_forums === true)
		{
			// Just grab all attachment info from database
			$sql = 'SELECT
						a.*,
						t.forum_id
					FROM
						' . ATTACHMENTS_TABLE . ' a,
						' . TOPICS_TABLE . ' t
					WHERE
						a.topic_id <> 0
						AND a.topic_id = t.topic_id
						' . $where . '
					ORDER BY
						filetime ' . ((!$config['display_order']) ? 'DESC' : 'ASC') . ', post_msg_id ASC';
			$result = $db->sql_query_limit($sql, $config['board3_attachments_number']);

			while ($row = $db->sql_fetchrow($result))
			{
				$size_lang = ($row['filesize'] >= 1048576) ? $user->lang['MIB'] : (($row['filesize'] >= 1024) ? $user->lang['KIB'] : $user->lang['BYTES']);
				$row['filesize'] = ($row['filesize'] >= 1048576) ? round((round($row['filesize'] / 1048576 * 100) / 100), 2) : (($row['filesize'] >= 1024) ? round((round($row['filesize'] / 1024 * 100) / 100), 2) : $row['filesize']);

				$replace = character_limit(utf8_substr($row['real_filename'], 0, strrpos($row['real_filename'], '.')), $config['board3_attach_max_length']);

				$template->assign_block_vars('attach', array(
					'FILESIZE'			=> $row['filesize'] . ' ' . $size_lang,
					'FILETIME'			=> $user->format_date($row['filetime']),
					'DOWNLOAD_COUNT'	=> (int) $row['download_count'], // grab downloads count
					'FILENAME'			=> $replace,
					'REAL_FILENAME'		=> $row['real_filename'],
					'PHYSICAL_FILENAME'	=> basename($row['physical_filename']),
					'ATTACH_ID'			=> $row['attach_id'],
					'POST_IDS'			=> (!empty($post_ids[$row['attach_id']])) ? $post_ids[$row['attach_id']] : '',
					'POST_MSG_ID'		=> $row['post_msg_id'], // grab post ID to redirect to post
					'U_FILE'			=> append_sid($phpbb_root_path . 'download/file.' . $phpEx, 'id=' . $row['attach_id']),
					'U_TOPIC'			=> append_sid($phpbb_root_path . 'viewtopic.'.$phpEx, 'p='.$row['post_msg_id'].'#p'.$row['post_msg_id']),
				));
			}
			$db->sql_freeresult($result);

			$template->assign_var('S_DISPLAY_ATTACHMENTS', true);
		} 
		else 
		{
			$template->assign_var('S_DISPLAY_ATTACHMENTS', false);
		}

		return 'attachments.html';
	}

	function get_template_side($module_id)
	{
		global $config, $template, $db, $user, $auth, $phpEx, $phpbb_root_path;

		$attach_forums = false;
		$where = '';
		$filetypes = array();

		// Get filetypes and put them into an array
		if(isset($config['board3_attachments_filetype']) && strlen($config['board3_attachments_filetype']) > 0)
		{
			$filetypes = explode(',', $config['board3_attachments_filetype']);
		}

		if($config['board3_attachments_forum_ids'] !== '')
		{
			$attach_forums_config = (strpos($config['board3_attachments_forum_ids'], ',') !== false) ? explode(',', $config['board3_attachments_forum_ids']) : array($config['board3_attachments_forum_ids']);
			$forum_list =  array_unique(array_keys($auth->acl_getf('f_read', true)));
			
			if($config['board3_attachments_forum_exclude'])
			{
				$forum_list = array_unique(array_diff($forum_list, $attach_forums_config));
			}
			else
			{
				$forum_list =  array_unique(array_intersect($attach_forums_config, $forum_list));
			}
		}
		else
		{
			$forum_list =  array_unique(array_keys($auth->acl_getf('f_read', true)));
		}

		if(sizeof($forum_list))
		{
			$attach_forums = true;
			$where = 'AND ' . $db->sql_in_set('t.forum_id', $forum_list);
		}

		if(sizeof($filetypes))
		{
			if($config['board3_attachments_exclude'])
			{
				$where .= ' AND ' . $db->sql_in_set('a.extension', $filetypes, true);
			}
			else
			{
				$where .= ' AND ' . $db->sql_in_set('a.extension', $filetypes);
			}
		}

		if($attach_forums === true)
		{
			// Just grab all attachment info from database
			$sql = 'SELECT
						a.*,
						t.forum_id
					FROM
						' . ATTACHMENTS_TABLE . ' a,
						' . TOPICS_TABLE . ' t
					WHERE
						a.topic_id <> 0
						AND a.topic_id = t.topic_id
						' . $where . '
					ORDER BY
						filetime ' . ((!$config['display_order']) ? 'DESC' : 'ASC') . ', post_msg_id ASC';
			$result = $db->sql_query_limit($sql, $config['board3_attachments_number']);

			while ($row = $db->sql_fetchrow($result))
			{
				$size_lang = ($row['filesize'] >= 1048576) ? $user->lang['MIB'] : (($row['filesize'] >= 1024) ? $user->lang['KIB'] : $user->lang['BYTES']);
				$row['filesize'] = ($row['filesize'] >= 1048576) ? round((round($row['filesize'] / 1048576 * 100) / 100), 2) : (($row['filesize'] >= 1024) ? round((round($row['filesize'] / 1024 * 100) / 100), 2) : $row['filesize']);

				$replace = character_limit(utf8_substr($row['real_filename'], 0, strrpos($row['real_filename'], '.')), $config['board3_attach_max_length']);

				$template->assign_block_vars('attach', array(
					'FILESIZE'			=> $row['filesize'] . ' ' . $size_lang,
					'FILETIME'			=> $user->format_date($row['filetime']),
					'DOWNLOAD_COUNT'	=> (int) $row['download_count'], // grab downloads count
					'FILENAME'			=> $replace,
					'REAL_FILENAME'		=> $row['real_filename'],
					'PHYSICAL_FILENAME'	=> basename($row['physical_filename']),
					'ATTACH_ID'			=> $row['attach_id'],
					'POST_IDS'			=> (!empty($post_ids[$row['attach_id']])) ? $post_ids[$row['attach_id']] : '',
					'POST_MSG_ID'		=> $row['post_msg_id'], // grab post ID to redirect to post
					'U_FILE'			=> append_sid($phpbb_root_path . 'download/file.' . $phpEx, 'id=' . $row['attach_id']),
					'U_TOPIC'			=> append_sid($phpbb_root_path . 'viewtopic.'.$phpEx, 'p='.$row['post_msg_id'].'#p'.$row['post_msg_id']),
				));
			}
			$db->sql_freeresult($result);

			$template->assign_var('S_DISPLAY_ATTACHMENTS', true);
		} 
		else 
		{
			$template->assign_var('S_DISPLAY_ATTACHMENTS', false);
		}

		return 'attachments.html';
	}

	function get_template_acp($module_id)
	{
		return array(
			'title'	=> 'ACP_PORTAL_ATTACHMENTS_NUMBER_SETTINGS',
			'vars'	=> array(
				'legend1'							=> 'ACP_PORTAL_ATTACHMENTS_NUMBER_SETTINGS',
				'board3_attachments_number'	=> array('lang' => 'PORTAL_ATTACHMENTS_NUMBER'		 ,	'validate' => 'int',		'type' => 'text:3:3',		 'explain' => true),
				'board3_attach_max_length'	=> array('lang' => 'PORTAL_ATTACHMENTS_MAX_LENGTH'		 ,	'validate' => 'int',		'type' => 'text:3:3',		 'explain' => true),
				'board3_attachments_forum_ids'	=> array('lang' => 'PORTAL_ATTACHMENTS_FORUM_IDS',	'validate' => 'string',		'type' => 'custom',	'explain' => true,	'method' => 'select_forums', 'submit' => 'store_selected_forums'),
				'board3_attachments_forum_exclude' => array('lang' => 'PORTAL_ATTACHMENTS_FORUM_EXCLUDE', 'validate' => 'bool', 'type' => 'radio:yes_no',	 'explain' => true),
				'board3_attachments_filetype'	=> array('lang' => 'PORTAL_ATTACHMENTS_FILETYPE',	'validate' => 'string', 	'type' => 'custom',	'explain' => true,	'method' => 'select_filetype', 'submit' => 'store_filetypes'),
				'board3_attachments_exclude'	=> array('lang' => 'PORTAL_ATTACHMENTS_EXCLUDE', 	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => true),
			),
		);
	}

	/**
	* API functions
	*/
	function install($module_id)
	{
		set_config('board3_attachments_number', 8);
		set_config('board3_attach_max_length', 15);
		set_config('board3_attachments_forum_ids', '');
		set_config('board3_attachments_forum_exclude', 0);
		set_config('board3_attachments_filetype', '');
		set_config('board3_attachments_exclude', 0);
		return true;
	}

	function uninstall($module_id)
	{
		global $db;

		$del_config = array(
			'board3_attachments_number',
			'board3_attach_max_length',
			'board3_attachments_forum_ids',
			'board3_attachments_forum_exclude',
			'board3_attachments_filetype',
			'board3_attachments_exclude',
		);
		$sql = 'DELETE FROM ' . CONFIG_TABLE . '
			WHERE ' . $db->sql_in_set('config_name', $del_config);
		return $db->sql_query($sql);
	}
	
		// Create select box for attachment filetype
	function select_filetype($value, $key)
	{
		global $db, $user, $config;
		
		// Get extensions
		$sql = 'SELECT *
			FROM ' . EXTENSIONS_TABLE . '
			ORDER BY extension ASC';
		$result = $db->sql_query($sql);
		
		while ($row = $db->sql_fetchrow($result))
		{
			$extensions[] = $row;
		}
		
		$selected = array();
		if(isset($config['board3_attachments_filetype']) && strlen($config['board3_attachments_filetype']) > 0)
		{
			$selected = explode(',', $config['board3_attachments_filetype']);
		}
		
		// Build options
		$ext_options = '<select id="' . $key . '" name="' . $key . '[]" multiple="multiple">';
		foreach ($extensions as $id => $ext)
		{
			$ext_options .= '<option value="' . $ext['extension'] . '"' . ((in_array($ext['extension'], $selected)) ? ' selected="selected"' : '') . '>' . $ext['extension'] . '</option>';
		}
		$ext_options .= '</select>';
		
		return $ext_options;
	}
	
	// Store selected filetypes
	function store_filetypes($key)
	{
		global $db, $cache;
		
		// Get selected extensions
		$values = request_var($key, array(0 => ''));
		
		$filetypes = implode(',', $values);
		
		set_config('board3_attachments_filetype', $filetypes);

	}
	
	// Create forum select box
	function select_forums($value, $key)
	{
		global $user, $config;

		$forum_list = make_forum_select(false, false, true, true, true, false, true);
		
		$selected = array();
		if(isset($config[$key]) && strlen($config[$key]) > 0)
		{
			$selected = explode(',', $config[$key]);
		}
		// Build forum options
		$s_forum_options = '<select id="' . $key . '" name="' . $key . '[]" multiple="multiple">';
		foreach ($forum_list as $f_id => $f_row)
		{
			$s_forum_options .= '<option value="' . $f_id . '"' . ((in_array($f_id, $selected)) ? ' selected="selected"' : '') . (($f_row['disabled']) ? ' disabled="disabled" class="disabled-option"' : '') . '>' . $f_row['padding'] . $f_row['forum_name'] . '</option>';
		}
		$s_forum_options .= '</select>';

		return $s_forum_options;

	}
	
	// Store selected forums
	function store_selected_forums($key)
	{
		global $db, $cache;
		
		// Get selected extensions
		$values = request_var($key, array(0 => ''));
		
		$news = implode(',', $values);
		
		set_config($key, $news);
	
	}
}

?>