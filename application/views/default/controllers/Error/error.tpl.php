<!DOCTYPE html>
<html>
    <head>
        <title>Oupsss</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <header style="border:2px solid #ff0000; width:450px; margin:0px auto;padding:5px;background-color:#ffffff">
            <span style="font-weight:bold;text-align:center;color:#ff0000">Une erreur s'est produite :</span>
            <p><b><?php echo $this->error->type; ?></b> : <?php echo $this->error->message; ?></p>
        </header>
        <section style="border:2px solid #ff0000; width:450px; margin:3px auto;padding:5px;background-color:#ffffff">
            <span style="font-weight:bold;text-align:center;color:#ff0000">Debug :</span>
            <p><b>Type d'erreur</b> : <?php echo $this->error->type; ?></p>
            <p><b>Code erreur</b> : <?php echo $this->error->code; ?></p>
            <p><b>Fichier</b> : <?php echo $this->error->file; ?></p>
            <p><b>Ligne </b> : <?php echo $this->error->line; ?></p>
            <p><b>Message</b> : <?php echo $this->error->message; ?></p>
        </section>

    </body>
</html>