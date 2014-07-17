$(document).ready(function (){

    if ( typeof(BDWP_CaptchaImageRenderCheck) == "undefined" ) {

    	var BDWP_CaptchaImageRenderCheck = function () {

    		this.urlCaptchaImage	= $('#BDUrlCaptchaImage').val();
    		this.pluginFolder		= $('#BDPluginFolder').val();
    		this.loadingImage       = '<div class="updated" style="border: none"><p><img src="' + this.pluginFolder +'public/images/loading.gif"> ' + $('#BDMsgLoadingRenderCheck').val() + '</p></div>';
    	}

    	BDWP_CaptchaImageRenderCheck.prototype.CaptchaImageRenderCheck = function () {

    		var progressUrl = this.pluginFolder + 'handlers/captcha_provider_installation_handler.php';
	        var request = jQuery.ajax({
	            type  : 'GET',
	            url   : this.urlCaptchaImage,
	            async : true,
	            cache : false
	        });
	        
	        $('#lblMessageStatus').html(this.loadingImage);

	        request.done(function (data, textStatus, xhr) {

	            var contentType = xhr.getResponseHeader ("Content-Type");
	            var contentLength = xhr.getResponseHeader ("Content-Length");
	            var statusCode = xhr.status;

	            if ( contentType && contentLength && statusCode && !BDWP_CaptchaImageRenderCheck.prototype.IsCaptchaImage.call(this, statusCode, contentLength, contentType) ) {
	                BDWP_CaptchaImageRenderCheck.prototype.DisableLoginForm.call(this, progressUrl);
	            } else {
	                BDWP_CaptchaImageRenderCheck.prototype.InstallationEndedBotDetectLibrary.call(this, progressUrl);
	            }
	        });
	        
	        request.fail(function (xhr, textStatus) {
				BDWP_CaptchaImageRenderCheck.prototype.DisableLoginForm.call(this, progressUrl);
	        });
    	}

    	BDWP_CaptchaImageRenderCheck.prototype.DisableLoginForm = function (progressUrl) {
    		
	        var request = jQuery.ajax({
	            type  : 'POST',
	            url   : progressUrl,
	            data  : { BDOptions : BDWP_CaptchaImageRenderCheck.prototype.GetCookie.call(this, 'BotDetectOptions') },
	            async : true,
	            cache : false
	        });
	        
	        request.done(function (data) {
	            var jsonData = jQuery.parseJSON(data);
	            if ( jsonData.status == 'OK' ) {
	                var xhtml = '<div class="error"><p><strong>' + $('#BDMsgImageRenderError').val() + '</strong></p></div>';
	                $('#lblMessageStatus').html(xhtml);
	            }
	        });
			
			request.fail(function (xhr, textStatus) { $('#lblMessageStatus').html('') });
    	}

    	BDWP_CaptchaImageRenderCheck.prototype.InstallationEndedBotDetectLibrary = function (progressUrl) {

    		var request = jQuery.ajax({
	            type  : 'POST',
	            url   : progressUrl,
	            data  : { InstallationEnded : 'ended' },
	            async : true,
	            cache : false
	        });

	        request.done(function (data) {  $('#lblMessageStatus').html('') });
	        request.fail(function (xhr, textStatus) {  $('#lblMessageStatus').html('') });
    	}

    	BDWP_CaptchaImageRenderCheck.prototype.IsCaptchaImage = function (statusCode, contentLength, contentType) {
    		
    		var typeImages = ['image/jpeg', 'image/gif', 'image/png'];
        	return (statusCode == 200 && jQuery.inArray(contentType, typeImages) != -1 && contentLength > 1000)? true : false;
    	}


    	BDWP_CaptchaImageRenderCheck.prototype.GetCookie = function (cookieName) {
    		
    		cookieName = cookieName + "=";
	        var allCookie = document.cookie;
	        if ( allCookie.length > 0 )
	        {
	            var cookieArr = allCookie.split(';');
	            for ( var i = 0; i < cookieArr.length; i++ ) {
	                var tempCookie = $.trim(cookieArr[i]);
	                if ( tempCookie.search(cookieName) == 0 )  
	                    return tempCookie.substring(cookieName.length, tempCookie.length);
	            }
	        }
	        return "";
    	}

    	var validObj = new BDWP_CaptchaImageRenderCheck();
    	validObj.CaptchaImageRenderCheck();
    }
});