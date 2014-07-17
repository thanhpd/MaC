window.onload = function () {

    var messages = { 
		installing : document.getElementById('BDMsgWorkingInstallLib').value
	}
    var eBotDetectOptions  = document.getElementById('BDOptions');
    var wrong_email        = document.getElementById('wrong_email');
    var btnInstallLBD      = document.getElementById('btnInstallLBD');
    var btnInstallDisable  = document.getElementById('btnInstallDisable');
    var lblWaiting         = document.getElementById('lblWaiting');
	
	if (eBotDetectOptions != null) {
    	var BotDetectOptions = eBotDetectOptions.value;
    	SetCookie('BotDetectOptions', BotDetectOptions, 1);
    }

    if (btnInstallLBD != null) {
        if ( btnInstallDisable.addEventListener ) {
			btnInstallLBD.addEventListener('click', ProgressInstallLibrary, false);
		} else {
			btnInstallLBD.attachEvent('onclick', ProgressInstallLibrary);
		}
    }
     
    function ProgressInstallLibrary() {
        btnInstallLBD.style.display = "none";
        btnInstallDisable.style.display = "block";
        if (wrong_email != null) { wrong_email.innerHTML = '' };
        lblWaiting.innerHTML = messages.installing;
    }

    function SetCookie(cookieName, cookieValue, exDays) {
        var expires = "";
        if (exDays) {
            var date = new Date();
            date.setTime(date.getTime() + (exDays * 24 * 60 * 60 * 1000));
            expires = "expires=" + date.toGMTString();
        }
        document.cookie = cookieName + "=" + cookieValue + "; " + expires + "; path=/";
    }
}
