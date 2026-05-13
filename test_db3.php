<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ds = App\Models\DiklatPersonel::whereNotNull('kategori_sertifikat')->get();
foreach($ds as $d) {
    echo "ID: {$d->id}, Kategori: {$d->kategori_sertifikat}\n";
}
