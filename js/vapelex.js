function createCookie(name,value,days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name,"",-1);
}

function delete_cart_item(id) {
	var currentitems = JSON.parse(unescape(readCookie('vapelex_cart')));
	for(var i=0; i<currentitems.length; i++) {
		var det = currentitems[i][3];
		var det1 = det.replace(";", "a");
		var findid = currentitems[i][0] + det1;
		if(findid == id) {
			currentitems.splice(i, 1);
		}
		
	}
	var newarr = escape(JSON.stringify(currentitems));
	eraseCookie('vapelex_cart');
	createCookie('vapelex_cart', newarr, 30);
	var form_total = accounting.formatMoney(get_cart_total());
	$("#" + id).remove();
	$("#noitems").text(currentitems.length);
	$("#total1").text(form_total);
	$("#total2").text(form_total);
	if($(("#cart_item_" + id).length)){
		$("#cart_item_" + id).remove();
		$("#psubtotal").text(form_total);
		$("#ptotal").text(form_total);
	}
	
	
}
function get_cart_total() {
	var currentitems = JSON.parse(unescape(readCookie('vapelex_cart')));
	var total = 0.00;
	for(var i=0; i<currentitems.length; i++) {
			total = total + (currentitems[i][2] * currentitems[i][1]);
	}
	return total;
}

function update_cart() {
	var currentitems = JSON.parse(unescape(readCookie('vapelex_cart')));
	var newarr = new Array(currentitems.length);
	for(var i = 0; i < currentitems.length; i++) {
		newarr[i] = new Array(4);
	}
	var newtotal = 0.00;
	var newarrcount = 0;
	for(var i = 0; i<currentitems.length; i++) {
		var iid = currentitems[i][0];
		var details = currentitems[i][3];
		var id = iid + details.replace(";", "a");
		var newq = parseInt($("#quantity" + id).val());
		if(newq > 0) {
			//Round to two decimals for currency
			var price = currentitems[i][2];
			newarr[newarrcount][0] = currentitems[i][0];
			newarr[newarrcount][1] = newq;
			newarr[newarrcount][2] = price;
			newarr[newarrcount][3] = currentitems[i][3];
			newtotal = newtotal + (newarr[i][2] * newq);
			$("#dd_cart_q" + id).text("x" + newq);
			if($("#subtotal" + id).length) {
				$("#subtotal" + id).text(accounting.formatMoney(price * newq));
			}
		} else {
			delete_cart_item(id);
		}
		newarrcount++;
	}
	var form_total = accounting.formatMoney(newtotal);
	$("#noitems").text(newarr.length);
	$("#total1").text(form_total);
	$("#total2").text(form_total);
	$("#psubtotal").text(form_total);
	$("#ptotal").text(form_total);
	eraseCookie('vapelex_cart');
	createCookie('vapelex_cart', escape(JSON.stringify(newarr)), 30);
	
}
function selectStrength(strength) {
	$("#strength0").removeClass("active");
	$("#strength6").removeClass("active");
	$("#strength12").removeClass("active");
	$("#strength24").removeClass("active");
	$("#strength" + strength).addClass("active");
	$("#sstrength").val(strength + "mg");
}
function selectSize(size) {
	$("#size15").removeClass("active");
	$("#size30").removeClass("active");
	$("#size" + size).addClass("active");
	$("#ssize").val(size + "ml");
	
}	
function checkCoupon() {
	var code = $("#ccode").val();
	var discount = 0;
	var xmlhttp=new XMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
    if (xmlhttp.readyState==4 && xmlhttp.status==200) {
      if(xmlhttp.responseText != "false") {
		  alert('A discount of ' + xmlhttp.responseText + '% has been applied to your order!');
		  discount = parseInt(xmlhttp.responseText);
		  var currtotal = get_cart_total();
		  var discount = currtotal * (discount / 100);
		  var form_total = accounting.formatMoney(currtotal - discount);
  		  $("#ptotal").text(form_total);
		  $("#coupbutton").prop('disabled', true);
	  } else {
		  alert('Sorry, that code is invalid or has expired');
	  }
    }
  }
  xmlhttp.open("GET","checkcoupon.php?q="+code,true);
  xmlhttp.send();	
}
function fill_rating_form() {
	var rating = $("#rateit1").rateit("value");
	if(parseInt(rating) > 0) {
		$("#rating").val(rating);	
		$("#rateform").submit();
	} else {
		alert("Please select a value between 1 and 5");	
	}
}

function getShipEstimate() {
	var code = $("#zip").val();
	var estimate = 0.00;
	var xmlhttp=new XMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
    if (xmlhttp.readyState==4 && xmlhttp.status==200) {
      if(xmlhttp.responseText != "false") {
		  estimate = parseFloat(xmlhttp.responseText);
		  var estimate_formatted = accounting.formatMoney(parseFloat(xmlhttp.responseText));
		  var currtotal = get_cart_total();
		  var form_total = accounting.formatMoney(currtotal + estimate);
  		  $("#ptotal").text(form_total);
		  $("#shipest").text(estimate_formatted);
		  $("#zip").prop('disabled', true);
		  $("#estbutton").prop('disabled', true);
	  } else {
		  alert('Sorry, that zip code is invalid');
	  }
	}
  }
  if($("#zip").val().length == 5) {
	var service = $("#select-service").find("option:selected").val();
	xmlhttp.open("GET","libs/usps.php?q="+code+"&q2="+service,true);
 	xmlhttp.send();
  } else { alert('Sorry, that zip code is invalid'); }
}

function autoFill(fname, lname, email, address1, address2, city, state, zip) {
	if(!address2) address2 = "";
	$("#fname").val(fname);
	$("#lname").val(lname);
	$("#email1").val(email);
	$("#a1").val(address1);
	$("#a2").val(address2)
	$("#city").val(city);
	$("#zipcode").val(zip);
	$("#select-state option[text=" + state +"]").attr("selected","selected") ;
		
}

function shipToBill() {
	var fname = $("#fname").val();
	var lname = $("#lname").val();
	var email = $("#email1").val();
	var phone = $("#phone").val();
	var company = $("#company").val();
	var address1 = $("#a1").val();
	var address2 = $("#a2").val();
	var city = $("#city").val();
	var postalcode = $("#zipcode").val();
	var state = $("#select-state").find(":selected").text();
	$("#bfname").val(fname);
	$("#blname").val(lname);
	$("#bemail").val(email);
	$("#bphone").val(phone);
	$("#bcompany").val(company);
	$("#ba1").val(address1);
	$("#ba2").val(address2)
	$("#bcity").val(city);
	$("#bzipcode").val(postalcode);
	$("select#bselect-state option").each(function() { this.selected = (this.text == state); });
}

function qlogin() {
	var email = $("#lemail").val();
	var password = $("#password").val();
	$("#femail").val(email);
	$("#fpassword").val(password);	
	$("#lform").submit();
}