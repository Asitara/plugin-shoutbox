<?php
/*
 * Project:     EQdkp Shoutbox
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:        http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       2008
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2009 Aderyn
 * @link        http://eqdkp-plus.com
 * @package     shoutbox
 * @version     $Rev$
 *
 * $Id$
 */

if (!defined('EQDKP_INC'))
{
  header('HTTP/1.0 404 Not Found');exit;
}

$systems_shoutbox = array(
  'pages' => array(
    'archive' => array(
      'name' => 'hptt_shoutbox_archive',
      'table_main_sub' => '%shoutbox_id%',
      'table_sort_dir' => 'desc',
      'page_ref' => 'archive.php',
      'show_select_boxes' => $user->check_auth('a_shoutbox_delete', false),
      'table_presets' => array(
        array('name' => 'sbdate', 'sort' => true,  'th_add' => 'align="center" width="120px"', 'td_add' => 'align="center" nowrap="nowrap"'),
        array('name' => 'sbname', 'sort' => true,  'th_add' => 'align="center" width="20%"',   'td_add' => 'nowrap="nowrap"'),
        array('name' => 'sbtext', 'sort' => false, 'th_add' => 'align="center"',               'td_add' => '')
      )
    ),
    'manage' => array(
      'name' => 'hptt_shoutbox_manage',
      'table_main_sub' => '%shoutbox_id%',
      'table_sort_dir' => 'desc',
      'page_ref' => 'manage.php',
      'show_select_boxes' => $user->check_auth('a_shoutbox_delete', false),
      'table_presets' => array(
        array('name' => 'sbdate', 'sort' => true,  'th_add' => 'align="center" width="120px"', 'td_add' => 'align="center" nowrap="nowrap"'),
        array('name' => 'sbname', 'sort' => true,  'th_add' => 'align="center" width="20%"',   'td_add' => 'nowrap="nowrap"'),
        array('name' => 'sbtext', 'sort' => false, 'th_add' => 'align="center"',               'td_add' => '')
      )
    ),
  )
);

?>