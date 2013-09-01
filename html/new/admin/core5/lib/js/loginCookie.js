function ntsSetCookie( name, value, expire ) {
	time = new Date();
	time.setTime( time.getTime() + expire );
	document.cookie = name + "=" + value + "; expires=" + time.toGMTString();
	return true;
	}

function ntsGetCookie( name ) {
	var cSIndex = document.cookie.indexOf( name );
	if (cSIndex == -1) return false;
	cSIndex = document.cookie.indexOf( name + "=" )
	if (cSIndex == -1) return false;
	var cEIndex = document.cookie.indexOf( ";", cSIndex + ( name + "=" ).length );
	if (cEIndex == -1) cEIndex = document.cookie.length;
	return document.cookie.substring( cSIndex + ( name + "=" ).length, cEIndex );
	}

function ntsDelCookie( name ) {
	if ( ntsGetCookie( name )) 
		document.cookie = name + "=; expires=Thu, 01-Jan-70 00:00:01 GMT";
	}

ntsSetCookie( "ntsTestCookie", "ntsTestCookie", 1000 * 60 * 60 * 24 * 31 );
if( ntsGetCookie( "ntsTestCookie" ) != "ntsTestCookie" ){
	jQuery("ntsLoginForm").style.display = "none";
	jQuery("ntsCookieAlert").style.display = "block";
	}
