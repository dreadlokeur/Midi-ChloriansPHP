<!DOCTYPE html>
<html>
    <head>
        <title>Oupsss</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <header style="border:2px solid #ff0000; width:450px; margin:0px auto;padding:5px;background-color:#ffffff">
            <span style="font-weight:bold;text-align:center;color:#ff0000">Une erreur s'est produite :</span>
            <p><b><?php echo $this->exception->type; ?></b> : <?php echo $this->exception->message; ?></p>
        </header>

        <section style="border:2px solid #ff0000; width:450px; margin:3px auto;padding:5px;background-color:#ffffff">
            <span style="font-weight:bold;text-align:center;color:#ff0000">Debug :</span>
            <p><b>Fichier</b> : <?php echo $this->exception->file; ?></p>
            <p><b>Ligne </b> : <?php echo $this->exception->line; ?></p>
            <p><b>Trace</b> :<br/><?php echo $this->exception->trace; ?></p>
        </section>
    </body>
</html>