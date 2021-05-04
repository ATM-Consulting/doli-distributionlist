var lastopendropdown = null;

// Click onto the link "link to" or "hamburger", toggle dropdown
$(".dropdown dt a").on('click', function () {
	console.log("toggle dropdown dt a");

	//$(this).parent().parent().find('dd ul').slideToggle('fast');
	$(this).parent().parent().find('dd ul').toggleClass("open");

	if ($(this).parent().parent().find('dd ul').hasClass("open")) {
		lastopendropdown = $(this).parent().parent().find('dd ul');
		//console.log(lastopendropdown);
	} else {
		// We closed the dropdown for hamburger selectfields
		if ($("input:hidden[name=formfilteraction]").val() == "listafterchangingselectedfields") {
			console.log("resubmit the form saved into lastopendropdown after clicking on hamburger");
			//$(".dropdown dt a").parents('form:first').submit();
			//$(".dropdown dt a").closest("form").submit();
			lastopendropdown.closest("form").submit();
		}
	}

	// Note: Did not find a way to get exact height (value is update at exit) so i calculate a generic from nb of lines
	heigthofcontent = 21 * $(this).parent().parent().find('dd div ul li').length;
	if (heigthofcontent > 300) heigthofcontent = 300; // limited by max-height on css .dropdown dd ul
	posbottom = $(this).parent().parent().find('dd').offset().top + heigthofcontent + 8;
	var scrollBottom = $(window).scrollTop() + $(window).height();
	diffoutsidebottom = (posbottom - scrollBottom);
	console.log("heigthofcontent="+heigthofcontent+", diffoutsidebottom (posbottom="+posbottom+" - scrollBottom="+scrollBottom+") = "+diffoutsidebottom);
	if (diffoutsidebottom > 0)
	{
		pix = "-"+(diffoutsidebottom+8)+"px";
		console.log("We reposition top by "+pix);
		$(this).parent().parent().find('dd').css("top", pix);
	}
});

// Click outside of any dropdown
$(document).bind('click', function (e) {

	var $clicked = $(e.target);	// This is element we click on
	if (!$clicked.parents().hasClass("dropdown")) {
		//console.log("close dropdown dd ul - we click outside");
		//$(".dropdown dd ul").hide();
		$(".dropdown dd ul").removeClass("open");

		if ($("input:hidden[name=formfilteraction]").val() == "listafterchangingselectedfields") {
			console.log("resubmit form saved into lastopendropdown after clicking outside of dropdown and having change selectlist from selectlist field of hamburger dropdown");
			//$(".dropdown dt a").parents('form:first').submit();
			//$(".dropdown dt a").closest("form").submit();
			lastopendropdown.closest("form").submit();
		}
	}
});
