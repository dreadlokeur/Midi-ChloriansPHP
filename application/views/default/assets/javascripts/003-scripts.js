(function($) {
    $.extend({
        refreshCaptcha: function() {
            captchaName = arguments[1];
            $.ajax({
                type: 'GET',
                url: arguments[0],
                dataType: 'json',
                success: function(datas) {
                    if (datas.imageUrl && $('#captcha-' + captchaName + '-scr-img').length > 0)
                        document.getElementById('captcha-' + captchaName + '-scr-img').src = datas.imageUrl + '/' + Math.floor(Math.random() * 100);

                    if (datas.audioUrl && $('#captcha-' + arguments[1] + '-scr-audio').length)
                        $('#captcha-' + arguments[1] + '-scr-audio').remove();
                    //document.getElementById('captcha-'+arguments[1]+'-scr-audio').src = datas.audioUrl+'/' +Math.floor(Math.random()*14);
                }
            });
        },
        playCaptcha: function() {
            if ($.browser.msie)
                return $('<embed id="captcha-' + arguments[1] + '-scr-audio" src="' + arguments[0] + '/' + Math.floor(Math.random() * 100) + '" hidden="true">').appendTo('body');
            else
                return $('<audio id="captcha-' + arguments[1] + '-scr-audio" src="' + arguments[0] + '/' + Math.floor(Math.random() * 100) + '" hidden="true" autoplay="true"></audio>').appendTo('body');
        }
    });
})(jQuery);
jQuery(document).ready(function($) {
    // language updater
    $('.updateLanguage').click(function() {
        var language = $(this).attr('id');
        if (language === $('meta[http-equiv=content-language]').attr("content"))
            return;
        $.ajax({
            type: 'GET',
            url: urls['language'] + language,
            dataType: 'json',
            success: function(datas) {
                if (datas.updated === true)
                    location.reload();
            }
        });
        return false;
    });
});