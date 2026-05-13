<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$d = App\Models\DiklatPersonel::first();
if($d) {
    $d->kategori_sertifikat = 'Sertifikat Profesi';
    $d->save();
    echo "SAVED " . $d->kategori_sertifikat;
} else {
    echo "NO DATA";
}
