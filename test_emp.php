<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$emp = \App\Employee::where('employee_id', '1008')->first();
if ($emp) {
    echo "Employee 1008 details:\n";
    echo "Name: " . $emp->name . "\n";
    echo "Is Locked: " . ($emp->is_locked ? 'true' : 'false') . "\n";
} else {
    echo "Employee 1008 not found in DB\n";
}
