<?php
//$before = microtime(true);

//require __DIR__ . '/../webiik/vendor/autoload.php'; // only for Webiik dev purpose
require __DIR__ . '/private/vendor/autoload.php';

// Bootstrap app
require __DIR__ . '/private/app/app.php';

//echo '<br/><br/>Peak memory usage: ' . (memory_get_peak_usage() / 1000000) . ' MB';
//echo '<br/>End memory usage: ' . (memory_get_usage() / 1000000) . ' MB';

//$after = microtime(true);
//echo '<br/><br/>Execution time: '. ($after-$before) . ' sec';

//print_r(get_defined_vars());