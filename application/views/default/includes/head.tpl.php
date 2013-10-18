<head>
    <title><?php echo $this->langs->site_name; ?><?php if ($this->title) echo ' - ' . $this->title; ?></title>
    <meta charset="<?php echo $this->template->charset; ?>">
    <!-- <meta name="viewport" content="width=1024" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="viewport" content="initial-scale=1, maximum-scale=1" />
    <meta name="viewport" content="width=device-width" />-->
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->template->charset; ?>" />
    <meta name="google-site-verification" content="<?php echo GOOGLE_VERIFICATION; ?>" />
    <meta name="Author" content="Dreadlokeur" />
    <meta name="Description" content="<?php if ($this->desc) echo $this->desc; else echo $this->langs->site_desc; ?>" />
    <meta name="keywords" content="<?php if ($this->keywords) echo $this->keywords; else $this->langs->site_keywords; ?>" />
    <meta http-equiv="Expires" content="24Oct 2018 23:59:59 GMT">
    <meta http-equiv="Cache-Control" content="public;max-age=315360000" />
    <meta name="Revisit-After" content="7 days" />
    <meta name="robots" content="index,follow" />
    <link rel="icon" href="<?php echo $this->template->assets->img['url']; ?>favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo $this->template->assets->img['url']; ?>favicon.ico" type="image/x-icon" />
    <?php if ($this->template->assets->css['cache']) { ?>
    <link rel="stylesheet" href="<?php echo $this->template->assets->css['cacheName']; ?>" type="text/css" media="screen">
    <?php } ?>
    <!--[if lt IE 7]>
            <div class='aligncenter'><a href="http://www.microsoft.com/windows/internet-explorer/default.aspx?ocid=ie6_countdown_bannercode"><img src="http://storage.ie6countdown.com/assets/100/images/banners/warning_bar_0000_us.jpg"border="0"></a></div>  
    <![endif]-->
    <!--[if lt IE 9]>
            <script src="<?php echo $this->template->assets->js['url']; ?>no-autoload/html5.js"></script>
            <link rel="stylesheet" href="<?php echo $this->template->assets->css['url']; ?>no-autoload/ie.css"> 
    <![endif]-->
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', '<?php echo GOOGLE_UA; ?>']);
        _gaq.push(['_trackPageview']);
        (function() {
            var ga = document.createElement('script');
            ga.type = 'text/javascript';
            ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ga, s);
        })();
    </script>
</head>