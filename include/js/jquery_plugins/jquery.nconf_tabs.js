//
// TABS (tabbed pages / forms)
//
jQuery.nconf_taps = function(pages) {
//function tabs(pages) {
	if (!pages.length) return;
	pages.addClass("dyn-tabs");
	pages.first().show();
	var tabNavigation = $('<ul />').insertBefore(pages.first());
	pages.each(function() {
		var listElement = $("<li />");
		var anchorElement = $("<a />");
		var label = $(this).attr("title") ? $(this).attr("title") : "Kein Label";
		anchorElement.text(label);
		anchorElement.attr("href", "#" + $(this).attr("id"));
		listElement.append(anchorElement);
		tabNavigation.append(listElement);
	});
	var items = tabNavigation.find("li");
	/*
	items.first().addClass("current");
	items.click(function() {
		items.removeClass("current");
		$(this).addClass("current");
		pages.hide();
		pages.eq($(this).index()).fadeIn("slow");
	});
	*/
}

