$(document).ready(function() { var e = $("#load-more-button"),
        a = 1,
        n = e.data("load-link"),
        o = e.data("container"),
        t = $("#" + o),
        s = !1;
    a > 2 && e.addClass("hidden-xs-up"), e.on("click", function() { 
    	return a <= 2 && 
    	!$(this).hasClass("loading") && 
    	!$(this).hasClass("last-page") && 
    	$.ajax({ 
    		type: "GET", 
    		url: n, 
    		dataType: "html", 
    		beforeSend: function() { 
    			e.addClass("loading") 
    		}, complete: function(n) { 
    			e.removeClass("loading"), 
    			200 == n.status && 
    			"" != n.responseText && (++a > 2 && 
    				e.addClass("hidden-xs-up"), 
    				$(n.responseText).length > 0 && 
    				(s = "function" == typeof t.isotope, $(n.responseText).each(function() { var e = $(this);
                    s ? t.imagesLoaded(function() { t.isotope(), t.append(e).isotope("appended", e).isotope("layout") }) : t.append(e) }))) } }), !1 }) });