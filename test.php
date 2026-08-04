<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$att = \App\Attendance::where('type', 1)->first();
echo "Type: " . $att->type . "\n";
echo "Entry Type: " . $att->entry_type . "\n";
echo "Entry Type Label: " . $att->entry_type_label . "\n";
