<?php
// Usage: php scripts/delete_user.php notinugummy@gmail.com
if ($argc < 2) {
    echo "Usage: php scripts/delete_user.php <email>\n";
    exit(1);
}
$email = $argv[1];
$dbPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';
if (!file_exists($dbPath)) {
    echo "Database file not found at: $dbPath\n";
    exit(1);
}
try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // count matching users
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $userCount = (int) $stmt->fetchColumn();

    // delete email_otps rows
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM email_otps WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $otpCount = (int) $stmt->fetchColumn();

    $delOtp = $pdo->prepare('DELETE FROM email_otps WHERE email = :email');
    $delOtp->execute([':email' => $email]);

    $delUser = $pdo->prepare('DELETE FROM users WHERE email = :email');
    $delUser->execute([':email' => $email]);

    echo "Deleted user rows: " . $delUser->rowCount() . "\n";
    echo "Deleted OTP rows: " . $delOtp->rowCount() . "\n";
    echo "Previous counts - users: $userCount, otps: $otpCount\n";
} catch (Exception $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(2);
}

echo "Done.\n";

