<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Backoffice' ?></title>
</head>

<body style="margin: 0; font-family: sans-serif;">

    <?php include 'sidebar.php'; ?>
    <?php include 'navbar.php'; ?>

    <div class="content" style="margin-left: 250px; padding: 80px 20px 20px;">
        <?php if (isset($content)) echo $content; ?>
    </div>
</body>

</html>
