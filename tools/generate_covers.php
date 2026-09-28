<?php
/**
 * Discora - Game Box Art Downloader & Generator
 * Downloads clean authentic box arts for 40 games (10 PS4, 10 PS5, 10 Xbox One, 10 Xbox Series X|S)
 */

$productsDir = dirname(__DIR__) . '/assets/images/products/';
if (!is_dir($productsDir)) {
    mkdir($productsDir, 0777, true);
}

// Curated authentic box art URLs for 40 games
$games = [
    // -------------------------------------------------------------
    // 1. PS4 GAMES (10)
    // -------------------------------------------------------------
    'ps4-plague-tale.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1r7h.jpg',
    'ps4-ghost-of-tsushima.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co2crj.jpg',
    'ps4-the-last-of-us.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1r7f.jpg',
    'ps4-bloodborne.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1r77.jpg',
    'ps4-god-of-war.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1tmu.jpg',
    'ps4-uncharted-4.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1r79.jpg',
    'ps4-horizon-zero-dawn.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co28rw.jpg',
    'ps4-spiderman.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1r78.jpg',
    'ps4-rdr2.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1q1f.jpg',
    'ps4-witcher-3.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1wyy.jpg',

    // -------------------------------------------------------------
    // 2. PS5 GAMES (10)
    // -------------------------------------------------------------
    'ps5-spiderman-2.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co6e4x.jpg',
    'ps5-gow-ragnarok.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co5s5v.jpg',
    'ps5-ac-mirage.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co59b4.jpg',
    'ps5-demons-souls.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co2kca.jpg',
    'ps5-ffxvi.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co64q7.jpg',
    'ps5-horizon-forbidden-west.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co3d2f.jpg',
    'ps5-ratchet-clank.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co2o82.jpg',
    'ps5-returnal.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co2kx2.jpg',
    'ps5-re4-remake.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co5p9d.jpg',
    'ps5-elden-ring.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co4jni.jpg',

    // -------------------------------------------------------------
    // 3. XBOX ONE GAMES (10)
    // -------------------------------------------------------------
    'xbox-one-gears-5.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1nc0.jpg',
    'xbox-one-forza-horizon-4.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1s9d.jpg',
    'xbox-one-halo-5.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1r8v.jpg',
    'xbox-one-quantum-break.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1r8y.jpg',
    'xbox-one-sunset-overdrive.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1r8u.jpg',
    'xbox-one-sea-of-thieves.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1r8w.jpg',
    'xbox-one-ori.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1vce.jpg',
    'xbox-one-tomb-raider.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1r8t.jpg',
    'xbox-one-cyberpunk.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1rft.jpg',
    'xbox-one-gta-v.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1x18.jpg',

    // -------------------------------------------------------------
    // 4. XBOX SERIES X|S GAMES (10)
    // -------------------------------------------------------------
    'xbox-series-forza-motorsport.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co6qom.jpg',
    'xbox-series-halo-infinite.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co2558.jpg',
    'xbox-series-starfield.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co676w.jpg',
    'xbox-series-forza-horizon-5.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co390f.jpg',
    'xbox-series-flight-sim.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co1x6b.jpg',
    'xbox-series-hellblade-2.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co7k5k.jpg',
    'xbox-series-stalker-2.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co2kcy.jpg',
    'xbox-series-alan-wake-2.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co671h.jpg',
    'xbox-series-diablo-4.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co676v.jpg',
    'xbox-series-dragons-dogma-2.png' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/co6968.jpg'
];

echo "Starting download of 40 authentic game cover images...\n";
$success = 0;

foreach ($games as $filename => $url) {
    $targetPath = $productsDir . $filename;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && strlen($data) > 1000) {
        file_put_contents($targetPath, $data);
        echo "[OK] Saved: $filename (" . round(strlen($data)/1024) . " KB)\n";
        $success++;
    } else {
        echo "[FAILED] $filename (HTTP $httpCode)\n";
    }
}

echo "\nCompleted! $success / " . count($games) . " images downloaded to assets/images/products/\n";
