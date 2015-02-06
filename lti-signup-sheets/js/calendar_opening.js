$(document).ready(function () {

	// ***************************
	// onload actions
	// ***************************



	// ***************************
	// Calendar datepicker
	// ***************************
	$("#openingUntilDate").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'mm/dd/yy',
		yearRange: '-4:+4'
	});


	// populate modal form with calendar date of day clicked
	$(document).on('click', '.addOpeningLink', function(){
		var dateClicked = $(this).attr('data-cal-date');
		setupModalForm(dateClicked);
	});

	function setupModalForm(forDateYYYYMMDD){

		var forDateAry = forDateYYYYMMDD.split('-');
		var forDateClean = forDateAry[1]+'/'+forDateAry[2]+'/'+forDateAry[0];

		var d = new Date(forDateYYYYMMDD);
		var dow = (['mon','tue','wed','thu','fri','sat','sun'])[d.getDay()];
		var dom = forDateAry[2] * 1;


		// set up the date
		$("#openingUntilDate").attr('value',forDateClean);

		$("#openingDateStart").val(forDateYYYYMMDD);
		$(".openingCalDate").html(forDateClean);

		// clear out & reset the day-of-week repeats
		$('.repeat_dow_val').val(0);
		$('.toggler_dow').removeClass('btn-success');
		$('.toggler_dow').removeClass('btn-default');
		$('.toggler_dow').addClass('btn-default');
		$('#btn_'+dow).click();

		// clear out & reset the day-of-month repeats
		$('.repeat_dom_val').val(0);
		$('.toggler_dom').removeClass('btn-success');
		$('.toggler_dom').removeClass('btn-default');
		$('.toggler_dom').addClass('btn-default');
		$('#btn_dom_'+dom).click();

		// set the repeat option to be the default (only on)
		$('#radioOpeningRepeatRate1').click();

		// hide the stuff that should be hidden
		$('#link_hide_duration').click();
		$('#link_hide_optional_opening_fields').click();

		// reset non-dynamic form fields to defaults
		$('#frmOpening').trigger("reset");
	}

	// ***************************
	// listeners
	// ***************************
	$("#link_show_optional_opening_fields").click(function () {
		$(".optional_opening_fields").show();
		$("#link_show_optional_opening_fields").hide();
	});
	$("#link_hide_optional_opening_fields").click(function () {
		$(".optional_opening_fields").hide();
		$("#link_show_optional_opening_fields").show();
	});


	// ***************************
	// default condition
	// ***************************
	$("#link_hide_time_range").click(function () {
		$(this).hide();
		$(".openings_by_time_range").hide();
		$("label[for='openingBeginTimeHour']").html("Starting&nbsp;at");
		$("label[for='openingEndTimeHour']").html("Make&nbsp;each&nbsp;opening");
		$("#link_hide_duration").show();
		$(".openings_by_duration").show();
		$("#openingTimeMode").val('duration');
	});

	$("#link_hide_duration").click(function () {
		$(this).hide();
		$(".openings_by_duration").hide();
		$("label[for='openingBeginTimeHour']").html("From");
		$("label[for='openingEndTimeHour']").html("To");
		$("#link_hide_time_range").show();
		$(".openings_by_time_range").show();
		$("#openingTimeMode").val('time_range');
	});

	$(".toggler_dow").click(function (event) {
		var which = event.target.id.substr(4, 3);
		if ($(this).hasClass("btn-success")) {
			//alert("turning off #repeat_dow_"+which);
			$(this).removeClass("btn-success").addClass("btn-default");
			$("#repeat_dow_" + which).val(0);
		}
		else {
			//alert("turning on #repeat_dow_"+which);
			$(this).addClass("btn-success").removeClass("btn-default");
			$("#repeat_dow_" + which).val(1);
		}
	});

	$(".toggler_dom").click(function (event) {
		var which = event.target.id.substr(8, 3);
		if ($(this).hasClass("btn-success")) {
			//alert("turning off #repeat_dom_"+which);
			$(this).removeClass("btn-success").addClass("btn-default");
			$("#repeat_dom_" + which).val(0);
		}
		else {
			//alert("turning on #repeat_dom_"+which);
			$(this).addClass("btn-success").removeClass("btn-default");
			$("#repeat_dom_" + which).val(1);
		}
	});

	$("#radioOpeningRepeatRate1").click(function (event) {
		//alert("on 1");
		$("#repeatWeekdayChooser").hide();
		$("#repeatMonthdayChooser").hide();
		$("#repeatUntilDate").hide();
	});

	$("#radioOpeningRepeatRate2").click(function (event) {
		//alert("on 2");
		$("#repeatWeekdayChooser").show();
		$("#repeatMonthdayChooser").hide();
		$("#repeatUntilDate").show();
	});

	$("#radioOpeningRepeatRate3").click(function (event) {
		//alert("on 3");
		$("#repeatWeekdayChooser").hide();
		$("#repeatMonthdayChooser").show();
		$("#repeatUntilDate").show();
	});


	$("#btn_save_openings").click(function (event) {
		if (($("#openingEndTimeHour").val() == '12')
			&& ($("#openingEndTimeMinute").val() == '0')
			&& ($("#openingEndTimeMinute_AMPM").val() == 'am')) {
			customAlert("", "cannot end an opening at 12:00 AM");
			return false;
		}

		// create start time string
		// create end time string
		var btime = valsToTimeString($("#openingBeginTimeHour").val(), $("#openingBeginTimeMinute").val(), $("#openingBeginTime_AMPM").val());
		var etime = valsToTimeString($("#openingEndTimeHour").val(), $("#openingEndTimeMinute").val(), $("#openingEndTimeMinute_AMPM").val());
		//alert("time strings are "+btime+" and "+etime);

		// if end <= start, that's a problem
		if (etime <= btime) {
			customAlert("", "end time must be later than start time");
			return false;
		}
		return true;
	});


	// ***************************
	// Cancel and cleanup
	// ***************************
	// TODO - update other modal cleanUpForm fxns with solutions from this one

	function cleanUpForm(formName) {
		// reset form to initial values (does not effect hidden inputs)
		$('#' + formName).trigger("reset");

		// TODO - temporary... need to deal with hidden values, and button conditions after click, dismiss, and re-open of modal
		// TODO - also need to bind  button[data-dismiss="modal"]  action (AND ESCAPE KEY TOO) to the cleanUpForm()
		//$(":input", form).each(function () {
		//	var type = this.type;
		//	var tag = this.tagName.toLowerCase();
		//	if (type == 'text') {
		//		this.value = "";
		//	}
		//});
		//validateOpening.resetForm();
		// manually remove input highlights
		// $(".form-group").removeClass('success').removeClass('error');
	}

	$('#btnOpeningCancel').click(function () {
		cleanUpForm("frmOpening");

		// manually clear modal values
		//$("#openingID").val(0);
		//$("#openingLabel").text('');
		//$("#openingAction").val('');
		//$("#frmOpening textarea").val('');
		//$("#frmOpening input[type=text]").val('');
		//$("#frmOpening input[type=radio]").attr("checked", false);
		//$("#frmOpening select").val(0);

		// reset submit button (avoid disabled state)
		$("#btnOpeningSubmit").button('reset');
	});

	// TODO - implement in ajax success callback
	//success: function (data) {
	//	// hide and reset form
	//	$("#btnOpeningCancel").click();

	// END: Cancel and cleanup

});