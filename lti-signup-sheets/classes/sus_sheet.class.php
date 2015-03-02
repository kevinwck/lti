<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class SUS_Sheet extends Db_Linked {
		public static $fields = array('sheet_id', 'created_at', 'updated_at', 'flag_delete', 'owner_user_id', 'sheetgroup_id', 'name', 'description', 'type', 'date_opens', 'date_closes', 'max_total_user_signups', 'max_pending_user_signups', 'flag_alert_owner_change', 'flag_alert_owner_signup', 'flag_alert_owner_imminent', 'flag_alert_admin_change', 'flag_alert_admin_signup', 'flag_alert_admin_imminent', 'flag_private_signups');
		public static $primaryKeyField = 'sheet_id';
		public static $dbTable = 'sus_sheets';
		public static $entity_type_label = 'sus_sheet';

		public $openings;
		public $access;

		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with this object
			//			$this->flag_workflow_published = false;
			//			$this->flag_workflow_validated = false;
			$this->openings = array();
			$this->access   = array();
		}

		public static function createNewSheet($owner_user_id, $dbConnection) {
			return new SUS_Sheet([
					'sheet_id'                  => 'NEW',
					'created_at'                => util_currentDateTimeString_asMySQL(),
					'updated_at'                => util_currentDateTimeString_asMySQL(),
					'flag_delete'               => FALSE,
					'owner_user_id'             => $owner_user_id,
					'sheetgroup_id'             => 0,
					'name'                      => '',
					'description'               => '',
					'type'                      => '',
					'date_opens'                => util_currentDateTimeString_asMySQL(),
					'date_closes'               => util_currentDateTimeString_asMySQL(),
					'max_total_user_signups'    => -1,
					'max_pending_user_signups'  => -1,
					'flag_alert_owner_change'   => 0,
					'flag_alert_owner_signup'   => 0,
					'flag_alert_owner_imminent' => 0,
					'flag_alert_admin_change'   => 0,
					'flag_alert_admin_signup'   => 0,
					'flag_alert_admin_imminent' => 0,
					'flag_private_signups'      => 0,
					'DB'                        => $dbConnection]
			);
		}

		public function clearCaches() {
			$this->openings = array();
			$this->access   = array();
		}

		/* static functions */

		public static function cmp($a, $b) {
			if ($a->name == $b->name) {
				return 0;
			}
			return ($a->name < $b->name) ? -1 : 1;
		}


		/* public functions */

		// cache provides data while eliminating unnecessary DB calls
		public function cacheOpenings() {
			if (!$this->openings) {
				$this->loadOpenings();
			}
		}

		// load explicitly calls the DB (generally called indirectly from related cache fxn)
		public function loadOpenings() {
			$this->openings = [];
			$this->openings = SUS_Opening::getAllFromDb(['sheet_id' => $this->sheet_id], $this->dbConnection);
			usort($this->openings, 'SUS_Opening::cmp');
		}

		// cache provides data while eliminating unnecessary DB calls
		public function cacheAccess() {
			if (!$this->access) {
				$this->loadAccess();
			}
		}

		// load explicitly calls the DB (generally called indirectly from related cache fxn)
		public function loadAccess() {
			$this->access = [];
			$this->access = SUS_Access::getAllFromDb(['sheet_id' => $this->sheet_id], $this->dbConnection);
			usort($this->access, 'SUS_Access::cmp');
		}

		// mark this object as deleted as well as any lower dependent items
		public function cascadeDelete() {
			// mark sheet as deleted
			$this->doDelete();

			// for this sheet: fetch openings
			$this->cacheOpenings();

			// mark openings as deleted
			foreach ($this->openings as $opening) {
				$opening->cascadeDelete();
			}
		}

		// for this sheetgroup: determine total and future signup limits, as well as current counts of each
		// for this sheet: determine total and future signup limits, as well as current counts of each
		public function renderAsHtmlUsageDetails($UserId = 0) {
			$fetch_signups_in_openings_all    = 0;
			$fetch_signups_in_openings_future = 0;
			$fetch_signups_in_sheet_all       = 0;
			$fetch_signups_in_sheet_future    = 0;

			// 1) sheetgroup: determine number of signups on sheets in this sheetgroup
			$sg = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => $this->sheetgroup_id], $this->dbConnection);

			$sheets_in_sg         = SUS_Sheet::getAllFromDb(['sheetgroup_id' => $sg->sheetgroup_id], $this->dbConnection);
			$list_sheet_ids_in_sg = Db_Linked::arrayOfAttrValues($sheets_in_sg, 'sheet_id');

			$openings_in_sg_all         = SUS_Opening::getAllFromDb(['sheet_id' => $list_sheet_ids_in_sg], $this->dbConnection);
			$list_opening_ids_in_sg_all = Db_Linked::arrayOfAttrValues($openings_in_sg_all, 'opening_id');

			$openings_in_sg_future         = SUS_Opening::getAllFromDb(['sheet_id' => $list_sheet_ids_in_sg, 'begin_datetime >=' => util_currentDateTimeString_asMySQL()], $this->dbConnection);
			$list_opening_ids_in_sg_future = Db_Linked::arrayOfAttrValues($openings_in_sg_future, 'opening_id');

			if ($list_opening_ids_in_sg_all) {
				$fetch_signups_in_openings_all = count(SUS_Signup::getAllFromDb(['opening_id' => $list_opening_ids_in_sg_all, 'signup_user_id' => $UserId], $this->dbConnection));
			}
			if ($list_opening_ids_in_sg_future) {
				$fetch_signups_in_openings_future = count(SUS_Signup::getAllFromDb(['opening_id' => $list_opening_ids_in_sg_future, 'signup_user_id' => $UserId], $this->dbConnection));
			}

			// 2) sheet: determine number of signups on this sheet
			$openings_in_one_sheet_all         = SUS_Opening::getAllFromDb(['sheet_id' => $this->sheet_id], $this->dbConnection);
			$list_opening_ids_in_one_sheet_all = Db_Linked::arrayOfAttrValues($openings_in_one_sheet_all, 'opening_id');

			$openings_in_one_sheet_future         = SUS_Opening::getAllFromDb(['sheet_id' => $this->sheet_id, 'begin_datetime >=' => util_currentDateTimeString_asMySQL()], $this->dbConnection);
			$list_opening_ids_in_one_sheet_future = Db_Linked::arrayOfAttrValues($openings_in_one_sheet_future, 'opening_id');

			if ($list_opening_ids_in_one_sheet_all) {
				$fetch_signups_in_sheet_all = count(SUS_Signup::getAllFromDb(['opening_id' => $list_opening_ids_in_one_sheet_all, 'signup_user_id' => $UserId], $this->dbConnection));
			}
			if ($list_opening_ids_in_one_sheet_future) {
				$fetch_signups_in_sheet_future = count(SUS_Signup::getAllFromDb(['opening_id' => $list_opening_ids_in_one_sheet_future, 'signup_user_id' => $UserId], $this->dbConnection));
			}

			$rendered = "<div id=\"contents_usage_quotas\">";
			$rendered .= "You may use <span class=\"badge\">" . $this->sus_grammatical_max_signups($sg->max_g_total_user_signups) . "</span> across all sheets in this group, ";
			$rendered .= "of which <span class=\"badge\">" . $this->sus_grammatical_max_signups_less_verbose($sg->max_g_pending_user_signups) . "</span> may be for future times. ";
			$rendered .= "Currently you have used ";
			$rendered .= "<span class=\"badge\">" . $this->sus_grammatical_max_signups($fetch_signups_in_openings_all) . "</span> in this group, ";
			$rendered .= "<span class=\"badge\">" . $this->sus_grammatical_max_signups_less_verbose($fetch_signups_in_openings_future) . "</span> of which are in the future. ";
			$rendered .= "You may have";
			$rendered .= "<span class=\"badge\">" . $this->sus_grammatical_max_signups($this->max_total_user_signups) . "</span> on this sheet, of which ";
			$rendered .= "<span class=\"badge\">" . $this->sus_grammatical_max_signups_less_verbose($this->max_pending_user_signups) . "</span> may be for future times. ";
			$rendered .= "Currently you have ";
			$rendered .= "<span class=\"badge\">" . $this->sus_grammatical_max_signups($fetch_signups_in_sheet_all) . "</span> on this sheet, ";
			$rendered .= "<span class=\"badge\">" . $this->sus_grammatical_max_signups_less_verbose($fetch_signups_in_sheet_future) . "</span> of which are in the future.";
			$rendered .= "</div>";

			// echo doesUserHaveSignupsRemaining($sg->max_g_total_user_signups, $sg->max_g_pending_user_signups, $s->max_total_user_signups, $s->max_pending_user_signups, $fetch_signups_in_openings_all, $fetch_signups_in_openings_future, $fetch_signups_in_sheet_all, $fetch_signups_in_sheet_future);

			return $rendered;
		}

		// ***************************
		// helper functions
		// ***************************

		// grammar for usage details (verbose)
		private function sus_grammatical_max_signups($num) {
			if (intval($num) < 0) {
				return 'an unlimited number of signups';
			}
			else {
				if (intval($num) == 1) {
					return "1 signup";
				}
				else {
					return "$num signups";
				}
			}
		}

		// grammar for usage details (less verbose)
		private function sus_grammatical_max_signups_less_verbose($num) {
			if (intval($num) < 0) {
				return 'an unlimited number';
			}
			else {
				if (intval($num) == 1) {
					return "1";
				}
				else {
					return "$num";
				}
			}
		}

	}
