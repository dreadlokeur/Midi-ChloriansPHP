(function($) {
    $.extend({
        refreshCaptcha: function(url, name) {
            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'json',
                success: function(datas) {
                    if (datas.imageUrl && $('#captcha-' + name + '-scr-img').length > 0)
                        document.getElementById('captcha-' + name + '-scr-img').src = datas.imageUrl + '/' + Math.floor(Math.random() * 100);

                    if (datas.audioUrl && $('#captcha-' + name + '-scr-audio').length)
                        $('#captcha-' + name + '-scr-audio').remove();
                    //document.getElementById('captcha-'+arguments[1]+'-scr-audio').src = datas.audioUrl+'/' +Math.floor(Math.random()*14);
                }
            });
        },
        playCaptcha: function(url, name) {
            if ($.browser.msie)
                return $('<embed id="captcha-' + name + '-scr-audio" src="' + url + '/' + Math.floor(Math.random() * 100) + '" hidden="true">').appendTo('body');
            else
                return $('<audio id="captcha-' + name + '-scr-audio" src="' + url + '/' + Math.floor(Math.random() * 100) + '" hidden="true" autoplay="true"></audio>').appendTo('body');
        }
    });
})(jQuery);
jQuery(document).ready(function($) {
    // language updater
    $('.updateLanguage').click(function() {
        var language = $(this).attr('id');
        if (language === $('html').attr("lang"))
            return false;
        $.ajax({
            type: 'GET',
            url: urls['language'] + '/' + language,
            dataType: 'json',
            success: function(datas) {
                if (datas.updated === true)
                    window.location.replace(urls['index']);
            }
        });
        return false;
    });
});