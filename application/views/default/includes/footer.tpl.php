<footer>
    <p>© Copyright 2013 | Code & Design by <a href="mailto:dreadlokeur@gmail.com">Dread</a></p>
    <p>
        <a href="http://validator.w3.org/check?uri=referer"><img src="<?php echo $this->template->assets->img['url']; ?>html5.png" alt="Valid XHTML 5" height="31" width="88" /></a>
        <a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="<?php echo $this->template->assets->img['url']; ?>css.gif" alt="CSS Valide !" width="88" height="31"/></a>
        <a href="http://www.phpfrance.com/"><img src="<?php echo $this->template->assets->img['url']; ?>php.png" alt="PHP" width="88" height="31" /></a>
        <a href="http://www.mysql.fr/"><img src="<?php echo $this->template->assets->img['url']; ?>mysql.gif" alt="MySQL" width="88" height="31" /></a>
        <a href="http://fr.wikipedia.org/wiki/JavaScript"><img src="<?php echo $this->template->assets->img['url']; ?>javascript.png" alt="JavaScript" width="88" height="31" /></a>
    </p>
</footer>
<?php if (isset($this->template->assets->js['urls'])) { ?>
    <script type="text/javascript"><?php echo $this->template->assets->js['urls']; ?></script>
<?php } ?>
<?php if (isset($this->template->assets->js['langs'])) { ?>
    <script type="text/javascript"><?php echo $this->template->assets->js['langs']; ?></script>
<?php } ?>
<?php if ($this->template->assets->js['cache']) { ?>
    <script type="text/javascript" src="<?php echo $this->template->assets->js['cacheName']; ?>"></script>
<?php } ?>