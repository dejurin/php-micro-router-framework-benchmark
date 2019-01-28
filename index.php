<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PHP Micro & Router Framework Benchmark</title>
</head>
<body>
<h1>List of frameworks</h1>
<ul>
<?php
    if ($handle = opendir('./frameworks/')) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != ".." && is_dir(__DIR__.'/frameworks/'.$entry)) {
                echo '<li><a href="/php-micro-router-framework-benchmark/frameworks/'.$entry.'/">'.$entry."</a></li>".PHP_EOL;
            }
        }
        closedir($handle);
    }
?>
</ul>
<h2><a href="/php-micro-router-framework-benchmark/graph.php">Show graphs</a></h2>
<hr>
<footer>
<p style="text-align: right">This page is a part of <a href="https://github.com/dejurin/php-micro-router-framework-benchmark">php-micro-router-framework-benchmark</a>, powered by <a href="https://github.com/kenjis/php-framework-benchmark">php-framework-benchmark</a>.</p>
</footer>
</body>
</html>