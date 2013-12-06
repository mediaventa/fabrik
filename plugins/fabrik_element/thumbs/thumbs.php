<?php
/**
 * Plugin element to render thumbs-up/down widget
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.thumbs
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

/**
 * Plugin element to render thumbs-up/down widget
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.thumbs
 * @since       3.0
 */

class PlgFabrik_ElementThumbs extends PlgFabrik_Element
{
	/**
	 * States the element should be ignored from advanced search all queryes.
	 *
	 * @var bool  True, ignore in advanced search all.
	 */
	protected $ignoreSearchAllDefault = true;

	/**
	 * Shows the data formatted for the list view
	 *
	 * @param   string    $data      elements data
	 * @param   stdClass  &$thisRow  all the data in the lists current row
	 *
	 * @return  string	formatted value
	 */

	public function renderListData($data, stdClass &$thisRow)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$j3 = FabrikWorker::j3();
		$params = $this->getParams();
		$imagepath = COM_FABRIK_LIVESITE . 'plugins/fabrik_element/thumbs/images/';
		$data = FabrikWorker::JSONtoData($data, true);
		$listid = $this->getlistModel()->getTable()->id;
		$formModel = $this->getFormModel();
		$formid = $formModel->getId();

		$row_id = $thisRow->__pk_val;

		if (empty($data))
		{
			$data = array(0);
		}

		$str = '';

		for ($i = 0; $i < count($data); $i++)
		{
			$input->set('rowid', $row_id);
			$myThumb = $this->getMyThumb($listid, $formid, $row_id);
			$imagefileup = 'thumb_up_out.gif';
			$imagefiledown = 'thumb_down_out.gif';

			if ($myThumb == 'up')
			{
				$imagefileup = 'thumb_up_in.gif';
				$imagefiledown = 'thumb_down_out.gif';
			}
			elseif ($myThumb == 'down')
			{
				$imagefileup = 'thumb_up_out.gif';
				$imagefiledown = 'thumb_down_in.gif';
			}

			$count = $this->_renderListData($data[$i], $thisRow);
			$count = FabrikWorker::JSONtoData($count, true);
			$countUp = $count[0];
			$countDown = $count[1];
			$countDiff = $countUp - $countDown;
			$str = array();

			$upActiveClass = $myThumb === 'up' ? ' btn-success' : '';
			$downActiveClass = $myThumb === 'down' ? ' btn-danger' : '';

			if ($j3)
			{
				$str[] = '<div class="btn-group">';
				$str[] = '<button class="btn thumb-up' . $upActiveClass. '" data-fabrik-thumb="">';
				$str[] = '<span class="icon-thumbs-up"></span> <span class="thumb-count">' . $countUp . '</span></button>';

				if ($params->get('show_down', 1))
				{
					$str[] = '<button class="btn thumb-down' . $downActiveClass . '">';
					$str[] = '<span class="icon-thumbs-down"></span> <span class="thumb-count">' . $countDown . '</span></button>';
				}

				$str[] = '</div>';
			}
			else
			{
				$str[] = '<span style="color:#32d723;" id="count_thumbup' . $row_id . '">' . $countUp . '</span>';
				$str[] = '<img src="' . $imagepath . $imagefileup . '" style="padding:0px 5px 0 1px;" alt="UP" class="thumbup" id="thumbup' . $row_id . '"/>';
				$str[] = '<span style="color:#f82516;" id="count_thumbdown' . $row_id . '">' . $countDown . '</span>';
				$str[] = '<img src="' . $imagepath . $imagefiledown . '" style="padding:0px 5px 0 1px;" alt="DOWN" class="thumbdown" id="thumbdown' . $row_id . '"/>';
			}
			$data[$i] = implode("\n", $str);
		}

		$data = json_encode($data);

		return parent::renderListData($data, $thisRow);
	}

	/**
	 * Shows the data formatted for the list view
	 *
	 * @param   string  $data     elements data
	 * @param   object  $thisRow  all the data in the lists current row
	 *
	 * @return  string	formatted value
	 */

	private function _renderListData($data, $thisRow)
	{
		$params = $this->getParams();
		$user = JFactory::getUser();
		$list = $this->getlistModel()->getTable();
		$listid = $list->id;
		$formid = $list->form_id;
		$row_id = isset($thisRow->__pk_val) ? $thisRow->__pk_val : $thisRow->id;
		$db = FabrikWorker::getDbo();

		return $this->getThumbsCount($data, $listid, $formid, $row_id);
	}

	/**
	 * Get the # of likes
	 *
	 * @param   array   $data    Not used!
	 * @param   int     $listid  List id
	 * @param   int     $formid  Form id
	 * @param   string  $row_id  Row id
	 *
	 * @return count thumbs-up, count thumbs-down
	 */

	protected function getThumbsCount($data, $listid, $formid, $row_id)
	{
		$db = FabrikWorker::getDbo();
		$elementid = $this->getElement()->id;

		$db
			->setQuery(
				"SELECT COUNT(thumb) FROM #__{package}_thumbs WHERE listid = " . (int) $listid . " AND formid = " . (int) $formid . " AND row_id = "
					. $db->quote($row_id) . " AND element_id = " . (int) $elementid . " AND thumb = 'up'");
		$resup = $db->loadResult();
		$db
			->setQuery(
				"SELECT COUNT(thumb) FROM #__{package}_thumbs WHERE listid = " . (int) $listid . " AND formid = " . (int) $formid . " AND row_id = "
					. $db->quote($row_id) . " AND element_id = " . (int) $elementid . " AND thumb = 'down'");
		$resdown = $db->loadResult();

		return json_encode(array($resup, $resdown));
	}

	/**
	 * Determines if the element can contain data used in sending receipts,
	 * e.g. fabrikfield returns true
	 *
	 * @deprecated - not used
	 *
	 * @return  bool
	 */

	public function isReceiptElement()
	{
		return true;
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           To preopulate element with
	 * @param   int    $repeatCounter  Repeat group counter
	 *
	 * @return  string	Elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$name = $this->getHTMLName($repeatCounter);
		$app = JFactory::getApplication();
		$input = $app->input;
		$id = $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		$j3 = FabrikWorker::j3();

		if ($input->get('view') == 'form' && ((bool) $params->get('rate_in_from', false) === false || $this->getFormModel()->isNewRecord()))
		{
			return '';
		}

		$element = $this->getElement();
		$listid = $this->getlistModel()->getTable()->id;
		$formModel = $this->getFormModel();
		$formid = $formModel->getId();
		$row_id = $formModel->getRowId();

		if (!isset($thisRow))
		{
			$thisRow = new stdClass;
			$thisRow->__pk_val = $row_id;
		}

		$myThumb = $this->getMyThumb($listid, $formid, $row_id);

		// @TODO use Fabrikimage rather than hardwired image path
		$imagepath = COM_FABRIK_LIVESITE . 'plugins/fabrik_element/thumbs/images/';

		$str[] = '<div id="' . $id . '_div" class="fabrikSubElementContainer">';

		$imagefileup = 'thumb_up_out.gif';
		$imagefiledown = 'thumb_down_out.gif';

		if ($myThumb == 'up')
		{
			$imagefileup = 'thumb_up_in.gif';
			$imagefiledown = 'thumb_down_out.gif';
		}
		elseif ($myThumb == 'down')
		{
			$imagefileup = 'thumb_up_out.gif';
			$imagefiledown = 'thumb_down_in.gif';
		}

		$upActiveClass = $myThumb === 'up' ? ' btn-success' : '';
		$downActiveClass = $myThumb === 'down' ? ' btn-danger' : '';

		$id2 = FabrikString::rtrimword($id, '_ro');
		$count = $this->_renderListData($data[$id2], $thisRow);
		$count = FabrikWorker::JSONtoData($count, true);
		$countUp = $count[0];
		$countDown = $count[1];
		$countDiff = $countUp - $countDown;

		if ($j3)
		{
			$str[] = '<div class="btn-group">';
			$str[] = '<button class="btn thumb-up' . $upActiveClass. '">';
			$str[] = '<span class="icon-thumbs-up"></span> <span class="thumb-count">' . $countUp . '</span></button>';

			if ($params->get('show_down', 1))
			{
				$str[] = '<button class="btn thumb-down' . $downActiveClass . '">';
				$str[] = '<span class="icon-thumbs-down"></span> <span class="thumb-count">' . $countDown . '</span></button>';
			}

			$str[] = '</div>';
		}
		else
		{
			$str[] = '<span style="color:#32d723;" id="count_thumbup">' . $countUp . '</span>';
			$str[] = '<img src="' . $imagepath . $imagefileup . '" style="padding:0px 5px 0 1px;" alt="UP" id="thumbup"/>';

			if ($params->get('show_down', 1))
			{
				$str[] = '<span style="color:#f82516;" id="count_thumbdown">' . $countDown . '</span>';
				$str[] = '<img src="' . $imagepath . $imagefiledown . '" style="padding:0px 5px 0 1px;" alt="DOWN" id="thumbdown"/>';
			}
		}

		$str[] = '<input type="hidden" name="' . $name . '" id="' . $id . '" value="' . $countDiff . '" class="' . $id . '" />';
		$str[] = '</div>';

		return implode("\n", $str);
	}

	/**
	 * Manupulates posted form data for insertion into database
	 *
	 * @param   mixed  $val   this elements posted form data
	 * @param   array  $data  posted form data
	 *
	 * @return  mixed
	 */

	public function storeDatabaseFormat($val, $data)
	{
		$params = $this->getParams();
		$app = JFactory::getApplication();
		$input = $app->input;
		$listid = $input->getInt('listid');
		$formid = $input->getInt('formid');
		$row_id = $input->getString('rowid', '', 'string');

		if ($params->get('rating-mode') != 'creator-rating')
		{
			// $val = $this->getRatingAverage($val, $listid, $formid, $row_id);
		}

		return $val;
	}

	/**
	 * Get thumb value
	 *
	 * @param   int     $listid  List id
	 * @param   int     $formid  Form id
	 * @param   string  $row_id  Row id
	 *
	 * @return string  Thumb value
	 */

	protected function getMyThumb($listid, $formid, $row_id)
	{
		$db = FabrikWorker::getDbo();
		$elementid = $this->getElement()->id;
		$user = JFactory::getUser();
		$user_id = $user->get('id');
		$query = $db->getQuery(true);

		if ($user_id == 0)
		{
			$user_id = $this->getCookieName($listid, $row_id);
		}

		$query->select('thumb')->from('#__{package}_thumbs')
		->where('listid = ' . (int) $listid . ' AND formid = ' . (int) $formid . ' AND row_id = '
		. $db->quote($row_id) . ' AND element_id = ' . (int) $elementid . ' AND user_id = ' . $db->quote($user_id));
		$db->setQuery($query);
		$ret = $db->loadResult();

		return $ret;
	}

	/**
	 * Called via widget ajax, stores the selected thumb
	 * stores the diff (thumbs-up minus thumbs-down)
	 *
	 * @return  number  The new count for up and down
	 */

	public function onAjax_rate()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$this->setId($input->getInt('element_id'));
		$this->loadMeForAjax();

		$listid = $this->getListModel()->getId();
		$formid = $this->getFormModel()->getId();
		$row_id = $input->get('row_id');
		$thumb = $input->get('thumb');
		$add = $input->get('add', 'true');

		if ($add === 'true')
		{
			$this->doThumb($listid, $formid, $row_id, $thumb);
		}
		else
		{
			$this->deleteThumb($listid, $formid, $row_id, $thumb);
		}

		echo $this->getThumbsCount('', $listid, $formid, $row_id);
	}

	/**
	 * Get the cookie name
	 *
	 * @param   int     $listid  List id
	 * @param   string  $row_id  Row id
	 *
	 * @return  string
	 */

	private function getCookieName($listid, $row_id)
	{
		$cookieName = 'thumb-table_' . $listid . '_row_' . $row_id . '_ip_' . $_SERVER['REMOTE_ADDR'];
		jimport('joomla.utilities.utility');

		return JApplicationHelper::getHash($cookieName);
	}

	/**
	 * Main method to delete a rating
	 *
	 * @param   int     $listid  List id
	 * @param   int     $formid  Form id
	 * @param   string  $row_id  Row id
	 * @param   string  $thumb   Thumb value
	 *
	 * @return  void
	 */

	private function deleteThumb($listid, $formid, $row_id, $thumb)
	{
		$userid = $this->getUserId($listid, $row_id);
		$db = FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		$query->delete('#__{package}_thumbs')->where('user_id = ' . $db->quote($userid))
		->where('listid = ' . $listid . ' AND row_id = ' . $row_id . ' AND thumb = ' . $db->quote($thumb));
		$db->setQuery($query);
		$db->execute();
		$elementid = $this->getElement()->id;
		$this->updateDB($listid, $formid, $row_id, $elementid);
	}

	/**
	 * Get the user's id - if not logged in get uuid.
	 * If not logged in sets cookie as well
	 *
	 * @param   int     $listid  List id
	 * @param   string  $row_id  Row id
	 *
	 * @return  string
	 */
	private function getUserId($listid, $row_id)
	{
		$user = JFactory::getUser();
		$userid = (int) $user->get('id');

		if ($userid == 0)
		{
			$userid = $this->getCookieName($listid, $row_id);

			// Set cookie
			$lifetime = time() + 365 * 24 * 60 * 60;
			setcookie($userid, '1', $lifetime, '/');
		}

		return $userid;
	}

	/**
	 * Main method to store a rating
	 *
	 * @param   int     $listid  List id
	 * @param   int     $formid  Form id
	 * @param   string  $row_id  Row id
	 * @param   string  $thumb   Thumb value
	 *
	 * @return  void
	 */

	private function doThumb($listid, $formid, $row_id, $thumb)
	{
		if (!$this->canUse())
		{
			return;
		}

		$db = FabrikWorker::getDbo();
		$date = JFactory::getDate()->toSql();
		$userid = $this->getUserId($listid, $row_id);
		$elementid = $this->getElement()->id;
		$db->setQuery(
			"INSERT INTO #__{package}_thumbs
				(user_id, listid, formid, row_id, thumb, date_created, element_id)
				values (
					" . $db->quote($userid) . ",
					" . $db->quote($listid) . ",
					" . $db->quote($formid) . ",
					" . $db->quote($row_id) . ",
					" . $db->quote($thumb) . ",
					" . $db->quote($date) . ",
					" . $db->quote($elementid) . "
				)
				ON DUPLICATE KEY UPDATE
					date_created = " . $db->quote($date) . ",
					thumb = " . $db->quote($thumb)
		);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$err = new stdClass;
			$err->error = $e->getMessage();
			echo json_encode($err);
			exit;
		}

		$this->updateDB($listid, $formid, $row_id, $elementid);
	}

	/**
	 * Update the db record
	 *
	 * @param   int     $listid     List id
	 * @param   int     $formid     Form id
	 * @param   string  $row_id     Row ref
	 * @param   int     $elementid  Element id
	 *
	 * @return boolean
	 */
	private function updateDB($listid, $formid, $row_id, $elementid)
	{
		$db = FabrikWorker::getDbo();
		$db
			->setQuery(
				"UPDATE " . $this->getlistModel()->getTable()->db_table_name . "
                    SET " . $this->getElement()->name . " = ((SELECT COUNT(thumb) FROM #__{package}_thumbs WHERE listid = " . (int) $listid
					. " AND formid = " . (int) $formid . " AND row_id = " . $db->quote($row_id) . " AND element_id = " . (int) $elementid
					. " AND thumb = 'up') - (SELECT COUNT(thumb) FROM #__{package}_thumbs WHERE listid = " . (int) $listid . " AND formid = "
					. (int) $formid . " AND row_id = " . $db->quote($row_id) . " AND element_id = " . (int) $elementid
					. " AND thumb = 'down'))
                    WHERE " . $this->getlistModel()->getTable()->db_primary_key . " = " . $db->quote($row_id) . "
                        LIMIT 1");

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$err = new stdClass;
			$err->error = $e->getMessage();
			echo json_encode($err);
			exit;
		}

		return true;
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array
	 */

	public function elementJavascript($repeatCounter)
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$input = $app->input;
		$params = $this->getParams();

		if ($input->get('view') == 'form' && ((bool) $params->get('rate_in_from', false) === false || $this->getFormModel()->isNewRecord()))
		{
			return '';
		}

		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		$data = $this->getFormModel()->data;
		$listid = $this->getlistModel()->getTable()->id;
		$formModel = $this->getFormModel();
		$formid = $formModel->getId();
		$row_id = $formModel->getRowId();
		$opts = new stdClass;
		$opts->row_id = $row_id;
		$opts->myThumb = $this->getMyThumb($listid, $formid, $row_id);
		$opts->elid = $this->getElement()->id;
		$opts->userid = (int) $user->get('id');
		$opts->view = $input->get('view');
		$opts->listid = $listid;
		$opts->formid = $this->getFormModel()->getId();

		return array('FbThumbs', $id, $opts);
	}

	/**
	 * Get JS code for ini element list js
	 * Overwritten in plugin classes
	 *
	 * @return string
	 */

	public function elementListJavascript()
	{
		$user = JFactory::getUser();
		$params = $this->getParams();
		$user = JFactory::getUser();
		$id = $this->getHTMLId();
		$list = $this->getlistModel()->getTable();
		$formid = $list->form_id;
		$listMyThumbs = array();
		$idFromCookie = null;
		$data = $this->getListModel()->getData();
		$groupKeys = array_keys($data);

		foreach ($groupKeys as $gKey)
		{
			foreach ($data[$gKey] as $rowkey)
			{
				if (!$idFromCookie && $user->get('id') == 0)
				{
					$idFromCookie = $this->getCookieName($list->id, $rowkey->__pk_val);
				}

				$listMyThumbs[$rowkey->__pk_val] = $this->getMyThumb($list->id, $formid, $rowkey->__pk_val);
			}
		}

		if ($user->get('id') == 0)
		{
			$userid = $idFromCookie;
		}
		else
		{
			$userid = $user->get('id');
		}

		$opts = new stdClass;
		$opts->listid = $list->id;
		$opts->formid = $this->getFormModel()->getId();
		$opts->imagepath = COM_FABRIK_LIVESITE . 'plugins/fabrik_element/thumbs/images/';
		$opts->elid = $this->getElement()->id;
		$opts->myThumbs = $listMyThumbs;
		$opts->userid = $userid;
		$opts->renderContext = $this->getListModel()->getRenderContext();
		$opts = json_encode($opts);

		return "new FbThumbsList('$id', $opts);\n";
	}

	/**
	 * Used by radio and dropdown elements to get a dropdown list of their unique
	 * unique values OR all options - basedon filter_build_method
	 *
	 * @param   bool    $normal     do we render as a normal filter or as an advanced search filter
	 * @param   string  $tableName  table name to use - defaults to element's current table
	 * @param   string  $label      field to use, defaults to element name
	 * @param   string  $id         field to use, defaults to element name
	 * @param   bool    $incjoin    include join
	 *
	 * @return  array  text/value objects
	 */

	public function filterValueList($normal, $tableName = '', $label = '', $id = '', $incjoin = true)
	{
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$params = $this->getParams();
		$filter_build = $params->get('filter_build_method', 0);

		if ($filter_build == 0)
		{
			$filter_build = $usersConfig->get('filter_build_method');
		}

		if ($filter_build == 2)
		{
			return $this->filterValueList_All($normal, $tableName, $label, $id, $incjoin);
		}
		else
		{
			return $this->filterValueList_Exact($normal, $tableName, $label, $id, $incjoin);
		}
	}

	/**
	 * Create an array of label/values which will be used to populate the elements filter dropdown
	 * returns all possible options
	 *
	 * @param   bool    $normal     do we render as a normal filter or as an advanced search filter
	 * @param   string  $tableName  table name to use - defaults to element's current table
	 * @param   string  $label      field to use, defaults to element name
	 * @param   string  $id         field to use, defaults to element name
	 * @param   bool    $incjoin    include join
	 *
	 * @return  array	filter value and labels
	 */

	protected function filterValueList_All($normal, $tableName = '', $label = '', $id = '', $incjoin = true)
	{
		for ($i = 0; $i < 6; $i++)
		{
			$return[] = JHTML::_('select.option', $i);
		}

		return $return;
	}

	/**
	 * Render the element admin settings
	 *
	 * @param   array   $data           admin data
	 * @param   int     $repeatCounter  repeat plugin counter
	 * @param   string  $mode           how the fieldsets should be rendered currently support 'nav-tabs' (@since 3.1)
	 *
	 * @return  string	admin html
	 */

	public function onRenderAdminSettings($data = array(), $repeatCounter = null, $mode = null)
	{
		$this->install();

		return parent::onRenderAdminSettings($data, $repeatCounter, $mode);
	}

	/**
	 * Install the plugin db table
	 *
	 * @return  void
	 */

	public function install()
	{
		$db = FabrikWorker::getDbo();
		$query = "CREATE TABLE IF NOT EXISTS  `#__{package}_thumbs` (
	`user_id` VARCHAR( 255 ) NOT NULL ,
	`listid` INT( 6 ) NOT NULL ,
	`formid` INT( 6 ) NOT NULL ,
	`row_id` INT( 6 ) NOT NULL ,
	`thumb` VARCHAR( 255 ) NOT NULL,
	`date_created` DATETIME NOT NULL,
	`element_id` INT( 6 ) NOT NULL,
	 PRIMARY KEY ( `user_id` , `listid` , `formid` , `row_id`, `element_id` )
);";
		$db->setQuery($query);
		$db->execute();
	}
}
