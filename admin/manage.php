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
 * @copyright   2008-2011 Aderyn
 * @link        http://eqdkp-plus.com
 * @package     shoutbox
 * @version     $Rev$
 *
 * $Id$
 */

// EQdkp required files/vars
define('EQDKP_INC', true);
define('IN_ADMIN', true);
define('PLUGIN', 'shoutbox');

$eqdkp_root_path = './../../../';
include_once('./../includes/common.php');


// -- Plugin installed? -------------------------------------------------------
if (!$pm->check('shoutbox', PLUGIN_INSTALLED))
{
  message_die($user->lang('sb_plugin_not_installed'));
}

/*+----------------------------------------------------------------------------
  | ShoutboxManage
  +--------------------------------------------------------------------------*/
class ShoutboxManage extends page_generic
{
  /**
   * Constructor
   */
  public function __construct()
  {
    $handler = array(
      'sb_delete' => array('process' => 'delete', 'session_key' => true, 'check' => 'a_shoutbox_delete'),
    );
    parent::__construct('a_shoutbox_', $handler);

    $this->process();
  }

  /**
   * delete
   * Delete entries
   */
  public function delete()
  {
    global $in, $user, $shoutbox;

    $messages = array();

    $delete_ids = $in->getArray('selected_ids', 'int');
    if (is_array($delete_ids) && count($delete_ids) > 0)
    {
      foreach ($delete_ids as $delete_id)
      {
        $shoutbox->deleteShoutboxEntry($delete_id);
      }

      $messages[] = $user->lang('sb_delete_success');
    }

    $this->display($messages);
  }

  /**
   * display
   * Display the page
   *
   * @param  array   $messages  Array of Messages to output
   */
  public function display($messages=array())
  {
    global $core, $user, $pm, $jquery, $tpl, $pdh, $time, $in, $eqdkp_root_path, $SID;

    // -- Messages ------------------------------------------------------------
    if ($messages)
    {
      foreach($messages as $name)
      {
        $core->message($name, $user->lang('shoutbox'), 'green');
      }
    }

    // -- get shoutbox entries ------------------------------------------------
    $shoutbox_ids = $pdh->get('shoutbox', 'id_list', array());
    $shoutbox_out = array();


    // -- build 2D array with [year][month] -----------------------------------
    $date_array = array();
    foreach ($shoutbox_ids as $shoutbox_id)
    {
      $shoutbox_date       = $pdh->get('shoutbox', 'date', array($shoutbox_id));
      $shoutbox_date_year  = $time->date('Y', $shoutbox_date);
      $shoutbox_date_month = $time->date('m', $shoutbox_date);
      $date_array[$shoutbox_date_year][$shoutbox_date_month][] = $shoutbox_id;
    }


    // -- output date select on left side -------------------------------------
    foreach ($date_array as $year => $months)
    {
      $tpl->assign_block_vars('year_row', array(
        'YEAR' => $year
      ));

      foreach ($months as $month => $ids)
      {
        $tpl->assign_block_vars('year_row.month_row', array(
          'MONTH'     => $time->date('F', $time->mktime(0, 0, 0, $month, 1, $year)),
          'COUNT'     => count($ids),
          'LINK_VIEW' => $eqdkp_root_path.'plugins/shoutbox/admin/manage.php'.$SID.'&session_key='.$user->data['session_key'].'&amp;year='.$year.'&amp;month='.$month,
        ));
      }
    }


    // -- year/month select? --------------------------------------------------
    $page_title = '';
    if ($in->get('year') && $in->get('month'))
    {
      // add all shoutbox entries within date/month to the output array
      $shoutbox_out = $date_array[$in->get('year')][$in->get('month')];
      $url_suffix   = '&amp;year='.$in->get('year').'&amp;month='.$in->get('month');
      $page_title   = $time->date('F', $time->mktime(0, 0, 0, $in->get('month'), 1, $in->get('year'))).' '.$in->get('year');
    }
    // -- search? -------------------------------------------------------------
    else if ($in->get('search'))
    {
      // loop through all the shoutbox entries and try to find in either username or in text
      foreach ($shoutbox_ids as $shoutbox_id)
      {
        $text   = $pdh->get('shoutbox', 'text',           array($shoutbox_id));
        $name   = $pdh->get('shoutbox', 'usermembername', array($shoutbox_id));
        $search = $in->get('search');
        if (strpos($text, $search) !== false || strpos($name, $search) !== false)
          $shoutbox_out[] = $shoutbox_id;
        $url_suffix = '&amp;search='.sanitize($in->get('search'));
        $page_title = $user->lang('search').': '.sanitize($in->get('search'));
      }
    }
    // -- last month ----------------------------------------------------------
    else if (count($shoutbox_ids) > 0)
    {
      // show the last month only
      $shoutbox_date       = $pdh->get('shoutbox', 'date', array($shoutbox_ids[0]));
      $shoutbox_date_year  = $time->date('Y', $shoutbox_date);
      $shoutbox_date_month = $time->date('m', $shoutbox_date);
      $shoutbox_out = $date_array[$shoutbox_date_year][$shoutbox_date_month];
      $url_suffix   = '';
      $page_title   = $time->date('F', $time->mktime(0, 0, 0, $shoutbox_date_month, 1, $shoutbox_date_year)).' '.$shoutbox_date_year;
    }


    // -- pagination ----------------------------------------------------------
    // get total and start
    $start = $in->get('start', 0);
    $total_entries = count($shoutbox_out);
    $limit = 50;
    $end = min($start + $limit, $total_entries);
    // pagination
    $pagination = generate_pagination('manage.php'.$SID.'&session_key='.$user->data['session_key'].$url_suffix, $total_entries, $limit, $start);


    // -- display entries -----------------------------------------------------
    require_once($eqdkp_root_path.'plugins/shoutbox/includes/systems/shoutbox.esys.php');
    $hptt_sort       = $in->get('sort');
    $hptt_url_suffix = $url_suffix.($start > 0 ? '&amp;start='.$start : '');
    $hptt = $this->get_hptt($systems_shoutbox['pages']['manage'], $shoutbox_ids, $shoutbox_out, array());


    // -- Template ------------------------------------------------------------
    $jquery->selectall_checkbox('sb_delete_all', 'selected_ids[]');
    $jquery->Dialog('AboutShoutbox', $user->lang('sb_about_header'), array('url'=>'../about.php', 'width'=>'400', 'height'=>'250'));
    $tpl->assign_vars(array (
      // Form
      'SB_TABLE'          => $hptt->get_html_table($hptt_sort, $hptt_url_suffix, $start, $end),
      'SB_PAGE_TITLE'     => ($page_title != '') ? '&raquo; '.$page_title : '',

      // pagination
      'START'             => $start,
      'PAGINATION'        => $pagination,

      // credits
      'SB_INFO_IMG'       => '../images/credits/info.png',
      'L_CREDITS'         => $user->lang('sb_credits_part1').$pm->get_data('shoutbox', 'version').$user->lang('sb_credits_part2'),
    ));

    // -- EQDKP ---------------------------------------------------------------
    $core->set_vars(array (
      'page_title'    => $user->lang('shoutbox').' '.$user->lang('sb_manage_archive').' '.$page_title,
      'template_path' => $pm->get_data('shoutbox', 'template_path'),
      'template_file' => 'admin/manage.html',
      'display'       => true
    ));
  }
}

$shoutboxManage = new ShoutboxManage();

?>
