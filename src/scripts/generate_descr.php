<?php
$dir = ".";
$files = scandir($dir);

foreach ($files as $file) {
    if (!is_dir($dir . $file)) {
        $shift = 4;
        $date = substr($file, $shift + 6, 2) . '.' . substr($file, $shift + 4, 2) . '.' . substr($file, $shift + 0, 4);
        echo '$photos[] = array("file" => "' . $dir . $file . '", "caption" => "' . $date . ' ...");' . "\n";
    }
}