$(document).ready(function () {

	$("#input_sheet_name").focus().select();

	// ***************************
	// For performance reasons, the Tooltip and Popover data-apis are opt-in, meaning you must initialize them yourself.
	// ***************************
	$('[data-toggle="popover"]').popover();


	// ***************************
	// Calendar datepicker
	// ***************************
	$("#inputSheetDateStart, #inputSheetDateEnd").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'mm/dd/yy',
		yearRange: '-4:+4'
	});


	// New Sheet: set default date (today)
	if ($("#inputSheetDateStart").val() == "") {
		var today = new Date();
		$("#inputSheetDateStart").datepicker('setDate', today);

		var futureDate = new Date(today.getTime());
		futureDate.setMonth(futureDate.getMonth() + 1);
		$("#inputSheetDateEnd").datepicker('setDate', futureDate);
	}


	// TODO - Replace this with pre-bundled jquery validation.js
	// form validation
	$("#btnSheetInfoSubmit").click(function (event) {
		if (!($("#inputSheetName").val().match(/\S/))) {
			alert("Missing Information - You must enter a name for the sheet");
			$("#inputSheetName").focus();
			return false;
		}
		return true;
	});


	// ***************************
	// Listeners
	// ***************************

	// Edit Sheet Access: Who can see signups
	$("input[name=radioSignupPrivacy]").on("change", function () {
		// GLOBAL_confirmHandlerData = $(this).attr('id');
		GLOBAL_confirmHandlerReference = $(this).val();
		var params = {
			ajaxVal_Action: "editSheetAccess-flag-private-signups",
			ajaxVal_Edit_ID: $("#hiddenSheetID").val(),
			ajaxVal_Edit_Value: GLOBAL_confirmHandlerReference
		};
		updateSheetAccess(params);
	});

	// Edit Sheet Access: Who can see signups
	$(".access_by_course_ckboxes").on("change", function () {
		var doAction = 'editSheetAccess-access-by-course-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-course-add';
		}
		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: $("#hiddenSheetID").val(),
			ajaxVal_Edit_Value: $(this).attr('data-permval')
		};
		updateSheetAccess(params);
	});


	$(".access_by_instructor_ckboxes").on("change", function () {
		var doAction = 'editSheetAccess-access-by-instructor-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-instructor-add';
		}
		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: $("#hiddenSheetID").val(),
			ajaxVal_Edit_Value: $(this).attr('data-permval')
		};
		updateSheetAccess(params);
	});


	$("#access_by_role_teacher").on("change", function () {
		var doAction = 'editSheetAccess-access-by-role-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-role-add';
		}
		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: $("#hiddenSheetID").val(),
			ajaxVal_Edit_Value: 'teacher'
		};
		updateSheetAccess(params);
	});

	$("#access_by_role_student").on("change", function () {
		var doAction = 'editSheetAccess-access-by-role-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-role-add';
		}
		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: $("#hiddenSheetID").val(),
			ajaxVal_Edit_Value: 'student'
		};
		updateSheetAccess(params);
	});

	$("#access_by_any").on("change", function () {
		var doAction = 'editSheetAccess-access-by-any-remove';
		if ($(this).prop("checked")) {
			doAction = 'editSheetAccess-access-by-any-add';
		}
		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: $("#hiddenSheetID").val(),
			ajaxVal_Edit_Value: 'all'
		};
		updateSheetAccess(params);
	});


	$("#textAccessByUserList,#textAdminByUserList").on("change", function () {
		var eleClickedId = $(this).attr("id");
		var doAction = 'editSheetAccess-access-by-user';
		if (eleClickedId == 'textAdminByUserList') {
			doAction = 'editSheetAccess-admin-by-user';
		}
		var params = {
			ajaxVal_Action: doAction,
			ajaxVal_Edit_ID: $("#hiddenSheetID").val(),
			ajaxVal_Edit_Value: $(this).val()
		};
		updateSheetAccess(params);
	});


//	function handleBroadAccess()

	function updateSheetAccess(params) {
		//alert(ary['url'] + '\n ' + ary['ajax_action'] + '\n ' + ary['ajax_id'] + '\n' + ary['ajax_val']);
		//console.log('to remote url: '+remoteUrl);
		console.dir(params);
		// show status
		dfnUtil_setTransientAlert('progress', 'Saving...');
		$.ajax({
			type: 'GET',
			url: "../ajax_actions/ajax_actions.php",
			cache: false,
			data: params,
			dataType: 'json',
			error: function (req, textStatus, err) {
				dfnUtil_setTransientAlert('error', "error making ajax request: " + err.toString());
				console.dir(req);
				console.dir(textStatus);
				console.dir(err);
			},
			success: function (data) {
				if (data.status == 'success') {
					// remove element
					dfnUtil_setTransientAlert('success', 'Saved');
				}
				else {
					// error message
					dfnUtil_setTransientAlert('error', 'Error saving: ' + data.notes);
				}
			}
			//, complete: function(req,textStatus) {
			//	$("#"+target_id).prop("disabled", false);
			//}
		});
	}

});