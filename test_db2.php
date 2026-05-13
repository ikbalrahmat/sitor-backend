<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$d = App\Models\DiklatPersonel::orderBy('updated_at', 'desc')->first();
print_r(['id' => $d->id, 'kategori' => $d->kategori_sertifikat, 'jenis' => $d->jenis, 'nomor' => $d->nomor_sertifikat]);
