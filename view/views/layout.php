<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= $title ?></title>
</head>
<body>
    <?= $this->content('top') ?>
    <hr>
    <?= $this->content('bottom') ?>
</body>
</html>