<?php
	require_once(dirname(__FILE__) . '/head_ajax.php');

	# 		debugging
	//		$action        = isset($_REQUEST["ajax_Action"]) ? util_quoteSmart($_REQUEST["ajax_Action"]) : 0;
	//		$results['status']       = 'success';
	//		$results['which_action'] = $action;
	//		$results['html_output']  = 'smiling now';
	//		util_prePrintR($_REQUEST); exit;
	//		echo json_encode($_REQUEST); exit; // # Return JSON array


	#------------------------------------------------#
	# Fetch AJAX values
	#------------------------------------------------#
	// generic parameter values (used for many different types of values, from many forms, etc.)
	$action     = isset($_REQUEST["ajax_Action"]) ? util_quoteSmart($_REQUEST["ajax_Action"]) : 0;
	$primaryID  = isset($_REQUEST["ajax_Primary_ID"]) && is_numeric($_REQUEST["ajax_Primary_ID"]) ? $_REQUEST["ajax_Primary_ID"] : 0;
	$customData = isset($_REQUEST["ajax_Custom_Data"]) ? $_REQUEST["ajax_Custom_Data"] : 0;
	//	$sheetID = isset($_REQUEST["edit_SheetID"]) ? $_REQUEST["edit_SheetID"] : 0;
	//	$openingID = isset($_REQUEST["edit_SheetID"]) ? $_REQUEST["edit_SheetID"] : 0;

	// individually passed parameters
	$ownerUserID = isset($_REQUEST["ajax_OwnerUserID"]) && is_numeric($_REQUEST["ajax_OwnerUserID"]) ? $_REQUEST["ajax_OwnerUserID"] : 0;
	$name        = isset($_REQUEST["ajax_Name"]) ? util_quoteSmart($_REQUEST["ajax_Name"]) : 0;
	$description = isset($_REQUEST["ajax_Description"]) ? util_quoteSmart($_REQUEST["ajax_Description"]) : 0;
	$maxTotal    = isset($_REQUEST["ajax_MaxTotal"]) ? $_REQUEST["ajax_MaxTotal"] : 0;
	$maxPending  = isset($_REQUEST["ajax_MaxPending"]) ? $_REQUEST["ajax_MaxPending"] : 0;


	#------------------------------------------------#
	# Set default return value
	#------------------------------------------------#
	$results = [
		'status' => 'failure'
	];


	#------------------------------------------------#
	# Identify and process requested action
	#------------------------------------------------#
	//###############################################################
	if ($action == 'add-sheetgroup') {
		$sg = SUS_Sheetgroup::createNewSheetgroupForUser($USER->user_id, $name, $description, $DB);

		$sg->owner_user_id              = $ownerUserID;
		$sg->max_g_total_user_signups   = $maxTotal;
		$sg->max_g_pending_user_signups = $maxPending;
		$sg->updated_at                 = date("Y-m-d H:i:s");

		$sg->updateDb();
		//echo 'SG UPDATED:\n'; #dkc debug
		//util_prePrintR($sg); #dkc debug

		if (!$sg->matchesDb) {
			// error: matching record already exists
			$results["notes"] = "unable to create new group in database";
			echo json_encode($results);
			exit;
		}

		$sheetgroup = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => $sg->sheetgroup_id], $DB);

		if (!$sheetgroup->matchesDb) {
			// error: matching record already exists
			$results["notes"] = "unable to retrieve newly created group from database";
			echo json_encode($results);
			exit;
		}

		// output
		$results['status']       = 'success';
		$results['which_action'] = 'add-sheetgroup';
		$results['html_output']  = '';
		$results['html_output'] .= "<table class=\"table table-condensed table-bordered table-hover\"><tbody>";
		$results['html_output'] .= "<tr class=\"info\"><th class=\"col-sm-11\">";
		$results['html_output'] .= "<span id=\"display-name-sheetgroup-id-" . htmlentities($sheetgroup->sheetgroup_id, ENT_QUOTES, 'UTF-8') . "\">" . htmlentities($sheetgroup->name, ENT_QUOTES, 'UTF-8') . "</span>";
		$results['html_output'] .= "</th><th class=\"col-sm-1 text-right text-nowrap\">";
		$results['html_output'] .= "<a href=\"#modalSheetgroup\" id=\"btn-edit-sheetgroup-id-" . htmlentities($sheetgroup->sheetgroup_id, ENT_QUOTES, 'UTF-8') . "\" class=\"sus-edit-sheetgroup btn btn-xs btn-primary\" data-toggle=\"modal\" data-target=\"#modalSheetgroup\" data-for-sheetgroup-id=\"" . htmlentities($sheetgroup->sheetgroup_id, ENT_QUOTES, 'UTF-8') . "\" data-for-sheetgroup-name=\"" . htmlentities($sheetgroup->name, ENT_QUOTES, 'UTF-8') . "\" data-for-sheetgroup-description=\"" . htmlentities($sheetgroup->description, ENT_QUOTES, 'UTF-8') . "\" data-for-sheetgroup-max-total=\"" . htmlentities($sheetgroup->max_g_total_user_signups, ENT_QUOTES, 'UTF-8') . "\" data-for-sheetgroup-max-pending=\"" . htmlentities($sheetgroup->max_g_pending_user_signups, ENT_QUOTES, 'UTF-8') . "\" title=\"Edit group\"><i class=\"glyphicon glyphicon-pencil\"></i></a>&nbsp;";
		$results['html_output'] .= "<a href=\"#\" class=\"btn btn-xs btn-danger sus-delete-sheetgroup\" data-for-sheetgroup-id=\"" . htmlentities($sheetgroup->sheetgroup_id, ENT_QUOTES, 'UTF-8') . "\" title=\"Delete group and all sheets in it\"><i class=\"glyphicon glyphicon-trash\"></i> Group</a>&nbsp;";
		$results['html_output'] .= "</th></tr>";
		$results['html_output'] .= "<tr><td class=\"col-sm-12\" colspan=\"2\">";
		$results['html_output'] .= "<a href=\"" . APP_ROOT_PATH . "/app_code/sheets_edit_one.php?sheetgroup=" . htmlentities($sheetgroup->sheetgroup_id, ENT_QUOTES, 'UTF-8') . "&sheet=new\" class=\"btn btn-xs btn-primary\" title=\"Add a new sheet to this group\"><i class=\"glyphicon glyphicon-plus\"></i> Add sheet</a>";
		$results['html_output'] .= "</td></tr>";
		$results['html_output'] .= "</tbody></table>";
	}
	//###############################################################
	elseif ($action == 'edit-sheetgroup') {
		$sg = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => $primaryID], $DB);

		if (!$sg->matchesDb) {
			// error: no matching record found
			$results["notes"] = "no matching record found";
			echo json_encode($results);
			exit;
		}
		$sg->name                       = $name;
		$sg->description                = $description;
		$sg->max_g_total_user_signups   = $maxTotal;
		$sg->max_g_pending_user_signups = $maxPending;
		$sg->updated_at                 = date("Y-m-d H:i:s");

		$sg->updateDb();

		// output
		$results['status']       = 'success';
		$results['which_action'] = 'edit-sheetgroup';
		$results['html_output']  = '';
	}
	//###############################################################
	elseif ($action == 'delete-sheetgroup') {
		$sg = SUS_Sheetgroup::getOneFromDb(['sheetgroup_id' => $primaryID], $DB);

		if (!$sg->matchesDb) {
			// error: no matching record found
			$results["notes"] = "no matching record found";
			echo json_encode($results);
			exit;
		}

		// mark this object as deleted as well as any lower dependent items
		$sg->cascadeDelete();

		// output
		if ($sg->matchesDb) {
			$results['status'] = 'success';
		}
	}
	//###############################################################
	elseif ($action == 'delete-sheet') {
		$s = SUS_Sheet::getOneFromDb(['sheet_id' => $primaryID], $DB);

		if (!$s->matchesDb) {
			// error: no matching record found
			$results["notes"] = "no matching record found";
			echo json_encode($results);
			exit;
		}

		// mark this object as deleted as well as any lower dependent items
		$s->cascadeDelete();

		// output
		if ($s->matchesDb) {
			$results['status'] = 'success';
		}
	}
	//###############################################################
	elseif ($action == 'delete-opening') {

		// workflow: For this sheet: would you like to delete this opening, all openings for this day, this and future openings in this series, or all past and future openings in this series?
		switch ($customData) {
			case 0:
				// delete only this opening
				$o = SUS_Opening::getOneFromDb(['opening_id' => $primaryID], $DB);

				if (!$o->matchesDb) {
					// error: no matching record found
					$results["notes"] = "no matching record found";
					echo json_encode($results);
					exit;
				}

				// mark this object as deleted as well as any lower dependent items
				$o->cascadeDelete();

				// output
				if ($o->matchesDb) {
					$results['status']        = 'success';
					$results['customData']    = $customData;
					$results['updateIDs_ary'] = $o->opening_id;
				}

				break;
			case 1:
				// delete all openings for this single day
				$o = SUS_Opening::getOneFromDb(['opening_id' => $primaryID], $DB);

				if (!$o->matchesDb) {
					// error: no matching record found
					$results["notes"] = "no matching record found";
					echo json_encode($results);
					exit;
				}

				// get sheet_id, create 24 date range (only for this day)
				$o_begin_datetime = substr($o->begin_datetime, 0, 10) . ' 00:00:00';
				$o_end_datetime   = substr($o->begin_datetime, 0, 10) . ' 23:59:59';

				// get all openings from this sheet that begin on this one day (this includes openings that begin on this day and run over into the next day)
				$o_all = SUS_Opening::getAllFromDb(['sheet_id' => $o->sheet_id, 'begin_datetime >=' => $o_begin_datetime, 'begin_datetime <=' => $o_end_datetime], $DB);

				if (count($o_all) == 0) {
					// error: no matching records found
					$results["notes"] = "no matching records found";
					echo json_encode($results);
					exit;
				}

				// capture opening_id's for later DOM updates
				$updateIDs_ary = [];

				// mark each object as deleted as well as any lower dependent items
				foreach ($o_all as $opening) {
					$opening->cascadeDelete();
					array_push($updateIDs_ary, $opening->opening_id);
				}

				// output
				$results['status']        = 'success';
				$results['customData']    = $customData;
				$results['updateIDs_ary'] = $updateIDs_ary;

				break;
			case 2:
				// delete this and all future openings in this series
				$o = SUS_Opening::getOneFromDb(['opening_id' => $primaryID], $DB);

				if (!$o->matchesDb) {
					// error: no matching record found
					$results["notes"] = "no matching record found";
					echo json_encode($results);
					exit;
				}

				// get sheet_id, opening_group_id, begin_datetime (this opening and all future openings)
				// get all openings from this sheet that begin on this one day (this includes openings that begin on this day and run over into the next day)
				$o_all = SUS_Opening::getAllFromDb(['sheet_id' => $o->sheet_id, 'begin_datetime >=' => $o->begin_datetime, 'opening_group_id <=' => $o->opening_group_id], $DB);

				if (count($o_all) == 0) {
					// error: no matching records found
					$results["notes"] = "no matching records found";
					echo json_encode($results);
					exit;
				}

				// capture opening_id's for later DOM updates
				$updateIDs_ary = [];

				// mark each object as deleted as well as any lower dependent items
				foreach ($o_all as $opening) {
					$opening->cascadeDelete();
					array_push($updateIDs_ary, $opening->opening_id);
				}

				// output
				$results['status']        = 'success';
				$results['customData']    = $customData;
				$results['updateIDs_ary'] = $updateIDs_ary;

				break;
			case 3:
				// delete this and all past and future openings in this series
				$o = SUS_Opening::getOneFromDb(['opening_id' => $primaryID], $DB);

				if (!$o->matchesDb) {
					// error: no matching record found
					$results["notes"] = "no matching record found";
					echo json_encode($results);
					exit;
				}

				// 1. get sheet_id, opening_group_id
				// 2. get all openings from this sheet that begin on this one day (this includes openings that begin on this day and run over into the next day)
				$o_all = SUS_Opening::getAllFromDb(['sheet_id' => $o->sheet_id, 'opening_group_id' => $o->opening_group_id], $DB);

				if (count($o_all) == 0) {
					// error: no matching records found
					$results["notes"] = "no matching records found";
					echo json_encode($results);
					exit;
				}

				// capture opening_id's for later DOM updates
				$updateIDs_ary = [];

				// mark each object as deleted as well as any lower dependent items
				foreach ($o_all as $opening) {
					$opening->cascadeDelete();
					array_push($updateIDs_ary, $opening->opening_id);
				}

				// output
				$results['status']        = 'success';
				$results['customData']    = $customData;
				$results['updateIDs_ary'] = $updateIDs_ary;

				break;
			default:
				break;
		}
	}
	//###############################################################
	elseif ($action == 'delete-signup' || $action == 'delete-signup-from-edit-opening-modal') {
		$su = SUS_Signup::getOneFromDb(['signup_id' => $primaryID], $DB);

		if (!$su->matchesDb) {
			// error: no matching record found
			$results["notes"] = "no matching record found";
			echo json_encode($results);
			exit;
		}

		// mark this object as deleted as well as any lower dependent items
		$su->cascadeDelete();

		// output
		if ($su->matchesDb) {
			$results['status'] = 'success';
		}
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-flag-private-signups') {
		// values: $primaryID, $customData

		$s = SUS_Sheet::getOneFromDb(['sheet_id' => $primaryID], $DB);

		if (!$s->matchesDb) {
			// error: no matching record found
			$results["notes"] = "no matching record found";
			echo json_encode($results);
			exit;
		}

		// edit this object
		$s->flag_private_signups = $customData;
		$s->updateDB();

		// output
		if ($s->matchesDb) {
			$results['status'] = 'success';
		}
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-course-remove') {
		doAccessRemove('bycourse', $primaryID, $customData, $results);
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-course-add') {
		doAccessAdd('bycourse', $primaryID, $customData, $results);
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-instructor-remove') {
		doAccessRemove('byinstr', $primaryID, $customData, $results);
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-instructor-add') {
		doAccessAdd('byinstr', $primaryID, $customData, $results);
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-role-remove') {
		doAccessRemove('byrole', $primaryID, $customData, $results);
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-role-add') {
		doAccessAdd('byrole', $primaryID, $customData, $results);
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-any-remove') {
		doAccessRemove('byhasaccount', $primaryID, 'all', $results);
	}
	//###############################################################
	elseif ($action == 'editSheetAccess-access-by-any-add') {
		doAccessAdd('byhasaccount', $primaryID, 'all', $results);
	}
	//###############################################################
	elseif (($action == 'editSheetAccess-access-by-user') || ($action == 'editSheetAccess-admin-by-user')) {
		$access_type = 'byuser';
		if ($action == 'editSheetAccess-admin-by-user') {
			$access_type = 'adminbyuser';
		}
		// values: $primaryID, $customData

		// 1 clean incoming user name and split into array
		// 2 get existing 'byuser' records
		// 3 generate to-add and to-remove sets
		// 4 do adds
		// 5 do removes
		// 6 note results

		// 1 clean incoming user name and split into array
		$usernames_str = $customData;
		$usernames_str = preg_replace('/,/', ' ', $usernames_str); // convert commas to single white space
		$usernames_str = preg_replace('/\\s+/', ' ', $usernames_str); // convert all white space to single white space
		$usernames_str = preg_replace('/^\\s+|\\s+$/', '', $usernames_str); // trim leading and trailing space

		$usernames_ary = [];
		if ($usernames_str) {
			$usernames_ary = explode(' ', $usernames_str);
		}

		// 2 get existing byuser records
		$existing_access_records = SUS_Access::getAllFromDb(['sheet_id' => $primaryID, 'type' => $access_type], $DB);
		// create hash of usernames
		$existing_access_usernames = Db_Linked::arrayOfAttrValues($existing_access_records, 'constraint_data');

		// 3 generate to-add and to-remove sets
		$to_add = [];
		foreach ($usernames_ary as $username) {
			if (!in_array($username, $existing_access_usernames)) {
				array_push($to_add, $username);
			}
		}
		$to_remove = [];
		foreach ($existing_access_usernames as $username) {
			if (!in_array($username, $usernames_ary)) {
				array_push($to_remove, $username);
			}
		}

		$results["notes"] = '';

		// 4 do adds
		if (count($to_add) > 0) {
			// iterate through usernames; verify that each username actually exists in database
			foreach ($to_add as $username_to_add) {
				$check_user_exists = User::getOneFromDb(['username' => $username_to_add], $DB);
				if ($check_user_exists->matchesDb) {
					$access_record = SUS_Access::createNewAccess($access_type, $primaryID, 0, $username_to_add, $DB);
					$access_record->updateDb();
					if (!$access_record->matchesDb) {
						$results["notes"] .= "could not save access for " . htmlentities($username_to_add, ENT_QUOTES, 'UTF-8') . "<br />\n";
					}
				}
				else {
					// username does not exist
					$results["notes"] .= "invalid username: " . htmlentities($username_to_add, ENT_QUOTES, 'UTF-8') . "<br />\n";
				}
			}
		}

		// 5 do removes
		foreach ($to_remove as $username_to_remove) {
			$access_record = SUS_Access::getOneFromDb(['type' => $access_type, 'sheet_id' => $primaryID, 'constraint_data' => $username_to_remove], $DB);
			if (!$access_record->matchesDb) {
				$results["notes"] .= "no existing access record found for " . htmlentities($username_to_remove, ENT_QUOTES, 'UTF-8') . "<br />\n";
				continue;
			}
			$access_record->doDelete();

			$check_access_record = SUS_Access::getOneFromDb(['type' => $access_type, 'sheet_id' => $primaryID, 'constraint_data' => $username_to_remove], $DB);
			if ($check_access_record->matchesDb) {
				$results["notes"] .= "could not remove access for " . htmlentities($username_to_remove, ENT_QUOTES, 'UTF-8') . "<br />\n";
			}
		}

		// 6 note results
		if ($results["notes"]) {
			$results["notes"] = "<br />Problems saving one or more usernames:<br />\n" . $results["notes"];
		}
		else {
			$results['status'] = 'success';
		}
	}
	//###############################################################
	elseif ($action == 'sheet-opening-signup-add-me') {
		// get opening object
		$o = SUS_Opening::getOneFromDb(['opening_id' => $primaryID], $DB);
		if (!$o->matchesDb) {
			// error: no matching record found
			$results["notes"] = "that opening does not exist";
			echo json_encode($results);
			exit;
		}

		// SECURITY: enforce whether user may create a new signup
		if (!$USER->isUserAllowedToAddNewSignup($o->sheet_id)) {
			// error: user may not signup on this sheet group or sheet
			$results["notes"] = "you are already at your limit for signups on this sheet";
			echo json_encode($results);
			exit;
		}

		// check if submitted user already has a signup for this opening (specify: flag_delete = TRUE)
		$su = SUS_Signup::getOneFromDb(['opening_id' => $primaryID, 'signup_user_id' => $USER->user_id, 'flag_delete' => TRUE], $DB);

		// check if submitted user already has a signup for this opening
		if (!$su->matchesDb) {
			$su = SUS_Signup::getOneFromDb(['opening_id' => $primaryID, 'signup_user_id' => $USER->user_id], $DB);
		}

		// update or create signup record
		if ($su->matchesDb) {
			// update preexisting record
			$su->flag_delete    = 0;
			$su->updated_at     = util_currentDateTimeString_asMySQL();
			$su->opening_id     = $primaryID;
			$su->signup_user_id = $USER->user_id;

			$su->updateDb();

			if (!$su->matchesDb) {
				// update record failed
				$results["notes"] = "database error: could not update signup";
				echo json_encode($results);
				exit;
			}
		}
		else {
			// create new record
			$su = SUS_Signup::createNewSignup($DB);

			$su->opening_id     = $primaryID;
			$su->signup_user_id = $USER->user_id;

			$su->updateDb();

			if (!$su->matchesDb) {
				// create record failed
				$results["notes"] = "database error: could not save signup";
				echo json_encode($results);
				exit;
			}
		}

		// must get sheet object to enable render fxn
		$sheet = SUS_Sheet::getOneFromDb(['sheet_id' => $o->sheet_id], $DB);

		// output
		$results['status']                    = 'success';
		$results['html_render_opening']       = $o->renderAsHtmlOpeningWithLimitedControls($USER->user_id);
		$results['html_render_usage_alert']   = $sheet->renderAsHtmlUsageAlert($USER);
		$results['html_render_usage_details'] = $sheet->renderAsHtmlUsageDetails($USER);
	}
	//###############################################################
	elseif ($action == 'sheet-opening-signup-delete-me') {

		// check if submitted user already has a signup for this opening
		$su = SUS_Signup::getOneFromDb(['opening_id' => $primaryID, 'signup_user_id' => $USER->user_id], $DB);

		// get all signups for this opening
		$o = SUS_Opening::getOneFromDb(['opening_id' => $primaryID], $DB);

		if (!$o->matchesDb) {
			// error: no matching record found
			$results["notes"] = "that opening does not exist";
			echo json_encode($results);
			exit;
		}

		// delete signup record
		if ($su->matchesDb) {
			// update preexisting record
			$su->flag_delete    = 1;
			$su->updated_at     = util_currentDateTimeString_asMySQL();
			$su->opening_id     = $primaryID;
			$su->signup_user_id = $USER->user_id;

			$su->updateDb();

			if (!$su->matchesDb) {
				// update record failed
				$results["notes"] = "database error: could not update signup";
				echo json_encode($results);
				exit;
			}
		}

		// must get sheet object to enable render fxn
		$sheet = SUS_Sheet::getOneFromDb(['sheet_id' => $o->sheet_id], $DB);

		// output
		$results['status']                    = 'success';
		$results['html_render_opening']       = $o->renderAsHtmlOpeningWithLimitedControls($USER->user_id);
		$results['html_render_usage_alert']   = $sheet->renderAsHtmlUsageAlert($USER);
		$results['html_render_usage_details'] = $sheet->renderAsHtmlUsageDetails($USER);
	}
	//###############################################################
	elseif ($action == 'edit-opening-add-signup-user') {

		// 1. Is username valid (against big table of Williams usernames)
		$u = User::getOneFromDb(['username' => $name], $DB);

		if (!$u->matchesDb) {
			// error: no matching record found
			$results["notes"] = "that username does not exist";
			echo json_encode($results);
			exit;
		}

		// check if submitted user already has a signup for this opening
		$su = SUS_Signup::getOneFromDb(['opening_id' => $primaryID, 'signup_user_id' => $u->user_id], $DB);

		// update or create signup record
		if ($su->matchesDb) {
			// update preexisting record
			$su->flag_delete    = 0;
			$su->updated_at     = util_currentDateTimeString_asMySQL();
			$su->opening_id     = $primaryID;
			$su->signup_user_id = $u->user_id;
			$su->admin_comment  = $description;

			$su->updateDb();

			if (!$su->matchesDb) {
				// update record failed
				$results["notes"] = "database error: could not update signup";
				echo json_encode($results);
				exit;
			}
		}
		else {
			// create new record
			$su = SUS_Signup::createNewSignup($DB);

			$su->opening_id     = $primaryID;
			$su->signup_user_id = $u->user_id;
			$su->admin_comment  = $description;

			$su->updateDb();

			if (!$su->matchesDb) {
				// create record failed
				$results["notes"] = "database error: could not save signup";
				echo json_encode($results);
				exit;
			}

			#------------------------------------------------#
			// TODO - ADD_OR_COMPLETE_QUEUED_MESSAGE
			# BEGIN: now queue the message
			#------------------------------------------------#
			/*
			// TODO - MUST ADD SHEET ID TO DATA-ATTRIBUTES AND PASS IT HERE; AND replace this hardcoded values
			$s = SUS_Sheet::getOneFromDb(['sheet_id' => 601,], $DB);
			// cacheStructuredData($datetime=0, $opening_id=0, $signup_id=0);
			$s->cacheStructuredData(0, 0, 813);
			util_prePrintR($s->structured_data);

			$subject = 'Glow SUS - ' . $u->first_name . ' ' . $u->last_name . ' signed up for ' . $sheet->name;
			$body    = "Signup Confirmation: " . $u->first_name . ' ' . $u->last_name . '\nOpening: ' . date_format(new DateTime($opening->begin_datetime), "m/d/Y g:i A") . '\nOn Sheet: ' . $sheet->name . '.';

			foreach ($s->structured_data as $obj) {
				// Queue messages for:
				// Email owner on signup or cancel
				// Email owner on upcoming signup
				// Email admins on signup or cancel
				// Email admins on upcoming signup
				// TODO - Need to implement proper array values here
				if ($obj->$sheet->flag_alert_owner_signup || $obj->$sheet->flag_alert_owner_imminent || $obj->$sheet->flag_alert_admin_signup || $obj->$sheet->flag_alert_admin_imminent) {
					prep_for_QueuedMessage($DB, $u->user_id, $u->email, $subject, $body, $su->opening_id, $subject, $body, $opening->opening_id, $sheet->sheet_id);
				}
			}

			// TODO - Possibly move this to be a class function (and combine with others, if possible)
			function prep_for_QueuedMessage($DB, $usersArray, $subject, $body, $openingID = 0, $sheetID = 0) {
				// QueuedMessage::factory($db, $user_id, $target, $summary, $body, $opening_id = 0, $sheet_id = 0, $type = 'email' )
				$qm = QueuedMessage::factory($DB, $usersArray["userID"], $usersArray["email"], $subject, $body, $openingID, $sheetID);
				$qm->updateDb();

				if (!$qm->matchesDb) {
					// create record failed
					$results['notes'] = "database error: could not create queued message for signup";
					echo json_encode($results);
					exit;
				}
			}
			*/
			#------------------------------------------------#
			# END: now queue the message
			#------------------------------------------------#
		}

		// output
		$results['status']       = 'success';
		$results['which_action'] = 'edit-opening-add-signup-user';
		$results['html_output']  = "<li data-for-firstname=\"" . htmlentities($u->firstname, ENT_QUOTES, 'UTF-8') . "\" data-for-lastname=\"" . htmlentities($u->lastname, ENT_QUOTES, 'UTF-8') . "\" data-for-signup-id=\"" . htmlentities($su->signup_id, ENT_QUOTES, 'UTF-8') . "\">";
		$results['html_output'] .= "<a href=\"#\" class=\"sus-delete-signup\" data-bb=\"alert_callback\" data-for-signup-id=\"" . htmlentities($su->signup_id, ENT_QUOTES, 'UTF-8') . "\" title=\"Delete signup\"><i class=\"glyphicon glyphicon-remove\"></i> </a>&nbsp;";
		$results['html_output'] .= htmlentities($u->first_name, ENT_QUOTES, 'UTF-8') . " " . htmlentities($u->last_name, ENT_QUOTES, 'UTF-8') . "</li>";
	}
	//###############################################################
	elseif ($action == 'fetch-signups-for-opening-id') {

		// TODO - Create this at the class level, instead?

		// get all signups for this opening
		$o = SUS_Opening::getOneFromDb(['opening_id' => $primaryID], $DB);

		if (!$o->matchesDb) {
			// error: no matching record found
			$results["notes"] = "that opening does not exist";
			echo json_encode($results);
			exit;
		}

		// count how many openings belong to this opening_group_id (an opening is "repeating" if it has > 1 opening per opening_group_id)
		$countOpeningsInGroup_ary = [];
		$countOpeningsInGroup = count(SUS_Opening::getAllFromDb(['opening_group_id' => $o->opening_group_id], $DB));
		$countOpeningsInGroup_ary[$o->opening_group_id] = $countOpeningsInGroup;

		$results['html_render_opening'] = $o->renderAsHtmlOpeningWithFullControls($countOpeningsInGroup_ary);

		$o->cacheSignups();

		// create hash of signup user_ids
		$signupUserIdsAry = [];
		foreach ($o->signups as $signup) {
			if (!in_array($signup->signup_user_id, $signupUserIdsAry)) {
				array_push($signupUserIdsAry, $signup->signup_user_id);
			}
		}
		if (!$signupUserIdsAry) {
			$results['status']       = 'success';
			$results['which_action'] = 'fetch-signups-for-opening-id';
			$results['html_output']  = '<li>no signups</li>';
			echo json_encode($results);
			exit;
		}

		// fetch users
		$users_info = User::getAllFromDb(['user_id' => $signupUserIdsAry], $DB);

		$signups_list = "";
		foreach ($o->signups as $signup) {
			foreach ($users_info as $user) {
				if ($signup->signup_user_id == $user->user_id) {
					$signups_list .= $signup->renderAsListItemSignupWithControls($user);
				}
			}

		}

		// output
		$results['status']       = 'success';
		$results['which_action'] = 'fetch-signups-for-opening-id';
		$results['html_output']  = $signups_list;
	}
	//###############################################################


	#------------------------------------------------#
	# Helper functions
	#------------------------------------------------#
	function constraintForAccessTypeIsById($type) {
		return ($type == 'byinstr') || ($type == 'bygradyear');
	}

	function doAccessAdd($type, $sheetId, $constraintInfo, &$results) {
		global $DB;
		$access_record = '';
		if (constraintForAccessTypeIsById($type)) {
			$access_record = SUS_Access::createNewAccess($type, $sheetId, $constraintInfo, '', $DB);
		}
		else {
			$access_record = SUS_Access::createNewAccess($type, $sheetId, 0, $constraintInfo, $DB);
		}
		$access_record->updateDb();

		if (!$access_record->matchesDb) {
			$results["notes"] = "could not save that access";
			echo json_encode($results);
			exit;
		}
		$results['status'] = 'success';
	}

	function doAccessRemove($type, $sheetId, $constraintInfo, &$results) {
		global $DB;
		$access_record = '';
		if (constraintForAccessTypeIsById($type)) {
			$access_record = SUS_Access::getOneFromDb(['type' => $type, 'sheet_id' => $sheetId, 'constraint_id' => $constraintInfo], $DB);
		}
		else {
			$access_record = SUS_Access::getOneFromDb(['type' => $type, 'sheet_id' => $sheetId, 'constraint_data' => $constraintInfo], $DB);
		}

		if (!$access_record->matchesDb) {
			$results["notes"] = "no matching record found in database";
			echo json_encode($results);
			exit;
		}

		$access_record->doDelete();

		$check_access_record = '';
		if (constraintForAccessTypeIsById($type)) {
			$check_access_record = SUS_Access::getOneFromDb(['type' => $type, 'sheet_id' => $sheetId, 'constraint_id' => $constraintInfo], $DB);
		}
		else {
			$check_access_record = SUS_Access::getOneFromDb(['type' => $type, 'sheet_id' => $sheetId, 'constraint_data' => $constraintInfo], $DB);
		}

		// output
		if ($check_access_record->matchesDb) {
			$results["notes"] = "could not remove that access";
			echo json_encode($results);
			exit;
		}

		$results['status'] = 'success';
	}


	#------------------------------------------------#
	# Debugging output
	#------------------------------------------------#
	//	echo "<pre>"; print_r($_REQUEST); echo "</pre>"; exit();


	#------------------------------------------------#
	# Return JSON array
	#------------------------------------------------#
	echo json_encode($results);
	exit;

