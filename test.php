<?php
try {
    $req = new \Illuminate\Http\Request();
    $req->merge(['search' => 'BINU THOMAS']);
    $rows = \App\Attendance::select('*');
    $rows->where(function($q) use ($req) {
        $q->where('address', 'like', '%'.$req->search.'%')
          ->orWhere('device', 'like', '%'.$req->search.'%');
    });
    $rows->paginate(100);
    echo "Query OK\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
