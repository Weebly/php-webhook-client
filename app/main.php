<html>
    <head>
        <title>PHP Client App</title>
    </head>
    <body>
        <H2>PHP Client App</H2>
        <h4>Webhook Output:</h4>
        <pre>
            <?php
            // output messages file to main page
            echo file_get_contents($root_dir . '/messages/messages.txt');
            ?>
        </pre>
    </body>
</html>