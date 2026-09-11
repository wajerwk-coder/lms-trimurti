<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$rows = DB::table('assessment_criteria')
    ->select('id','name','weight','mata_praktik','kategori')
    ->orderBy('mata_praktik')
    ->orderBy('kategori')
    ->get();

$total = 0;
$lastMata = '';
foreach($rows as $r) {
    if ($lastMata !== $r->mata_praktik) {
        if ($lastMata !== '') echo "  TOTAL BOBOT: $total%\n\n";
        echo "=== {$r->mata_praktik} ===\n";
        $total = 0;
        $lastMata = $r->mata_praktik;
    }
    echo "  ID:{$r->id} | weight:{$r->weight}% | [{$r->kategori}] {$r->name}\n";
    $total += $r->weight;
}
if ($lastMata !== '') echo "  TOTAL BOBOT: $total%\n";
