<!DOCTYPE html>
<html>
    <?php include $this->template->path . 'includes' . DS . 'head.tpl.php'; ?>
    <body>

        <?php include $this->template->path . 'includes' . DS . 'header.tpl.php'; ?>
        <section>
            <h1 class="align-center margin-top-60"><?php echo $this->errorInfo['code'] . ' ' . $this->errorInfo['message']; ?></h1>
        </section>
        <?php include $this->template->path . 'includes' . DS . 'footer.tpl.php'; ?>
    </body>
</html>