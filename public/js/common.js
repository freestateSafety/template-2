
$(document).ready(function(){

	$("#site").append('<div id="productOverlay"></div>');

    $("#adminNavIcon").click( function(){
        $("#adminNavCont").toggleClass("show");
    });

    /* product modal load */
	$(".quickview-link, .quickview-link-icon").click(function(e){
        console.log("Quick View");
		e.preventDefault();

		var prodId = $(this).parent("div").attr("id");
		console.log("prodId: " + prodId);
		$("div#productOverlay").load($(this).data('url'), function(){
			$("#productOverlay").modal({
				overlayClose:true,
				autoPosition:true,
				onClose: function(dialog){
					$.modal.close();
					$("#productOverlay").html("");
				}
			});
		});
	});

    /* material add modal load */
    $("a.addMaterialLink").click(function(e){
		e.preventDefault
        var materialURL = "/admin/material_add.asp?cat_id=" + window.reqCatId + "&ptype_id=" + window.reqPType
        //console.log("materialURL:"+materialURL);

        $("div#productOverlay").load(materialURL, function(){
			$("#productOverlay").modal({
				overlayClose:true,
				autoPosition:true,
				onClose: function(dialog){
					$.modal.close();
					$("#productOverlay").html("");
                    location.reload();
				}
			});
		});
    });
    
    
    /*** Continue Shopping button functions **/
    $("#continueShopping").click(function(){
        var retURL = store.get('contShoppingURL');
        if ( typeof retURL !== 'undefined' ){
            window.location.href = retURL;
        } else {
            window.location.href = "/";
        }
    });
    /* store the url for return later */
    $("#overlay_addToCart, .linkToProduct").click(function(){
        var thisUrl = window.location.href;
        store.set('contShoppingURL',thisUrl);
    });
    /*** End continue shopping **/

    
    
	$("#menuIcon").click(function(){
		//slideOver
		$("#site").toggleClass("slideMenu");
		$("#menuContainer").toggleClass("slideMenu");
        $("#menuList").show();
        $("body,main").toggleClass("noVertScroll");
        $("#menuCloser").toggleClass("slideMenu").show();
	});
	$("#menuCloser").click(function(){
        $(".slideMenu").removeClass("slideMenu");
        $(".noVertScroll").removeClass("noVertScroll");
        $("#menuCloser").hide();
        $("#menuList").hide()
    });



    $("ul#menuList li").click(function(){
        var menuPos = $(this).position();
        if( $(this).hasClass("active") ){
            $(this).removeClass("active").next("li").removeClass("showMenu");
        }else{
            $("ul#menuList li.active").removeClass("active");
            $(".showMenu").removeClass("showMenu");
            if ( $(this).attr("id") === "nav_safetySigns") {
                $(this).addClass("active").next("li").addClass("showMenu").children("div.subnav").css("left",(menuPos.left-100)+"px");
            }else{
                $(this).addClass("active").next("li").addClass("showMenu").children("div.subnav").css("left",menuPos.left+"px");
            }
        }

    });
    
    $("#UPSmapLink").click(function(e){
        e.preventDefault();
        $("#UPSmap").toggleClass("hidden");
    });


    $("#adminNavCont li").click(function(){
         $("#adminNavCont").removeClass("show");
    });
    
    $("#search-icon").click(function(){
        console.log("search icon");
        var searchCont = $("#search-container");
        var position = searchCont.position();
        var isOnscreen = false;
        $("#search-container").toggleClass("onscreen");
        /*
        if (position.left == 0) {
            isOnscreen = true;
        }
        console.log("isOnscreen: " + isOnscreen );
        if( isOnscreen ){
            $("#search-container").removeClass("onscreen");
        } else {
            $("#search-container").addClass("onscreen");
        }
        */
    }); 
    

    function getPos(el) {
        // yay readability
        for (var lx=0, ly=0;
             el != null;
             lx += el.offsetLeft, ly += el.offsetTop, el = el.offsetParent);
        return {x: lx,y: ly};
    }

    var xhrReq = 0,
        searchTimeout = null;
    $('#search-field').keyup(function () {
        var input = this;
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        searchTimeout = setTimeout(function () {
            var search = $(input).val();

            if (search.length >= 3) {
                var sessionReq = ++xhrReq;
                $.getJSON('/product/search', {
                    'q': search,
                    'limit': 3
                }, function (data) {
                    console.log(sessionReq, xhrReq, data);
                });
            }
        }, 500);
    });

});