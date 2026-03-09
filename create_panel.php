<?php
$config = include('config.php');

$api_url = $config['api_url'];
$api_key = $config['api_key'];
$location_id = $config['location_id'];
$nest_id = $config['nest_id'];
$egg_id = $config['egg_id'];

$username = $_POST['username'];
$password = $_POST['password'];
$ram = $_POST['ram'];
$cpu = $_POST['cpu'];

function log_action($message) {
    file_put_contents('logs.txt', date('Y-m-d H:i:s')." | ".$message."\n", FILE_APPEND);
}

log_action("Membuat panel untuk user: $username");

$ch = curl_init("$api_url/users");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $api_key",
        "Content-Type: application/json",
        "Accept: Application/vnd.pterodactyl.v1+json"
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'email' => $username . '@gmail.com',
        'username' => $username,
        'first_name' => $username,
        'last_name' => 'User',
        'password' => $password
    ])
]);
$user_response = curl_exec($ch);
curl_close($ch);
$user_data = json_decode($user_response, true);

if (!isset($user_data['attributes']['id'])) {
    log_action("❌ Gagal membuat user: " . $user_response);
    die("Gagal membuat user.");
}

$user_id = $user_data['attributes']['id'];
log_action("✅ User dibuat dengan ID: $user_id");

$ch = curl_init("$api_url/servers");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $api_key",
        "Content-Type: application/json",
        "Accept: Application/vnd.pterodactyl.v1+json"
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'name' => $username . "_server",
        'user' => $user_id,
        'egg' => $egg_id,
        'docker_image' => 'ghcr.io/parkervcp/yolks:nodejs_18',
        'startup' => 'npm start',
        'environment' => [
            'USER_UPLOAD' => '1',
        ],
        'limits' => [
            'memory' => $ram == 'Unlimited' ? 0 : (int)$ram * 1024,
            'cpu' => $cpu == 'Unlimited' ? 0 : (int)$cpu * 100,
            'disk' => 10240
        ],
        'feature_limits' => ['databases' => 1, 'allocations' => 1],
        'allocation' => ['default' => 1],
        'start_on_completion' => true,
        'oom_disabled' => true,
        'external_id' => null,
        'nest' => $nest_id,
        'location' => $location_id
    ])
]);
$server_response = curl_exec($ch);
curl_close($ch);
log_action("📦 Server dibuat: " . $server_response);

echo "<script>
alert('✅ Panel berhasil dibuat untuk user $username!');
window.location.href='index.html';
</script>";
?>