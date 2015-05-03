$(document).click("click",function(e) {
	div=$(e.target).attr("div");
	if (div) {
		$("#"+div).show();
	}
});
$(document).on("click",".closebutton",function(e) {
	$(".popup").hide();
});
