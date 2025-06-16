<?php
$marker = '#MANAGED_BY_UI';

$cronJobs = [
    'alvin_am' => [
        'label' => 'Alvin Clock In',
        'default' => '30 7 * * *',
        'command' => 'cd /home/bjalex081999/whatsapp-sender && /usr/bin/node send.js'
    ],
    'alvin_pm' => [
        'label' => 'Alvin Clock Out',
        'default' => '30 16 * * *',
        'command' => 'cd /home/bjalex081999/whatsapp-sender && /usr/bin/node send.js'
    ],
    'def_am' => [
        'label' => 'Def Clock In',
        'default' => '00 7 * * *',
        'command' => 'cd /home/bjalex081999/whatsapp-sender && /usr/bin/node send.js'
    ],
    'def_pm' => [
        'label' => 'Def Clock Out',
        'default' => '00 16 * * *',
        'command' => 'cd /home/bjalex081999/whatsapp-sender && /usr/bin/node send.js'
    ],
    'backup_joblist' => [
        'label' => 'Backup Job List',
        'default' => '00 13 * * 1',
        'command' => 'cd /opt/lampp/htdocs/pasker/whatsapp-sender && /usr/bin/node send.js'
    ],
    'jobid_scrape' => [
        'label' => 'JOBID Scrape',
        'default' => '00 07 * * 1',
        'command' => '/home/bjalex081999/scrap/scrap/bin/python /home/bjalex081999/scrap/jobid.py >> /home/bjalex081999/scrap/jobid.log 2>&1'
    ],
    'jobid_send' => [
        'label' => 'JOBID Send',
        'default' => '10 07 * * 1',
        'command' => 'cd /home/bjalex081999/scrap/whatsapp-sender && /usr/bin/node send.js'
    ],
    'devjobsindo_scrape' => [
        'label' => 'DevJobsIndo Scrape',
        'default' => '10 07 * * 1',
        'command' => '/home/bjalex081999/scrap/scrap/bin/python /home/bjalex081999/scrap/devjobsindo.py >> /home/bjalex081999/scrap/devjobsindo.log 2>&1'
    ],
    'devjobsindo_send' => [
        'label' => 'DevJobsIndo Send',
        'default' => '25 07 * * 1',
        'command' => 'cd /home/bjalex081999/scrap/whatsapp-sender && /usr/bin/node send_devjobsindo.js'
    ],
    'makaryo_scrape' => [
        'label' => 'Makaryo Scrape',
        'default' => '25 07 * * 1',
        'command' => '/home/bjalex081999/scrap/scrap/bin/python /home/bjalex081999/scrap/makaryo_scrap.py >> /home/bjalex081999/scrap/makaryo.log 2>&1'
    ],
    'makaryo_send' => [
        'label' => 'Makaryo Send',
        'default' => '30 07 * * 1',
        'command' => 'cd /home/bjalex081999/scrap/whatsapp-sender && /usr/bin/node send_makaryo.js'
    ],
    'snaphunt_scrape' => [
        'label' => 'Snaphunt Scrape',
        'default' => '30 07 * * 1',
        'command' => '/home/bjalex081999/scrap/scrap/bin/python /home/bjalex081999/scrap/snaphunt_scraper.py >> /home/bjalex081999/scrap/snaphunt.log 2>&1'
    ],
    'snaphunt_send' => [
        'label' => 'Snaphunt Send',
        'default' => '25 08 * * 1',
        'command' => 'cd /home/bjalex081999/scrap/whatsapp-sender && /usr/bin/node send_snaphunt.js'
    ],
    'toploker_scrape' => [
        'label' => 'Toploker Scrape',
        'default' => '25 08 * * 1',
        'command' => '/home/bjalex081999/scrap/scrap/bin/python /home/bjalex081999/scrap/toploker.py >> /home/bjalex081999/scrap/toploker.log 2>&1'
    ],
    'toploker_send' => [
        'label' => 'Toploker Send',
        'default' => '35 08 * * 1',
        'command' => 'cd /home/bjalex081999/scrap/whatsapp-sender && /usr/bin/node send_toploker.js'
    ],
];

$currentCron = shell_exec('crontab -l');
$currentLines = explode("\n", $currentCron);
$existingJobs = [];

foreach ($currentLines as $line) {
    if (strpos($line, $marker) !== false) {
        foreach ($cronJobs as $key => $job) {
            if (strpos($line, $job['command']) !== false) {
                $parts = preg_split('/\s+/', $line, 6);
                $existingJobs[$key] = implode(' ', array_slice($parts, 0, 5));
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newCrontab = [];

    // Keep non-UI jobs
    foreach ($currentLines as $line) {
        if (strpos($line, $marker) === false && trim($line) !== '') {
            $newCrontab[] = $line;
        }
    }

    foreach ($cronJobs as $key => $job) {
        if (isset($_POST["enable_$key"])) {
            $schedule = trim($_POST["schedule_$key"]);
            if (preg_match('/^(\S+\s+){4}\S+$/', $schedule)) {
                $newCrontab[] = "$schedule {$job['command']} $marker";
            }
        }
    }

    // Save to crontab
    $tmpFile = '/tmp/cron_update.txt';
    file_put_contents($tmpFile, implode("\n", $newCrontab) . "\n");
    shell_exec("sudo -u bjalex081999 /usr/bin/crontab $tmpFile");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cron Job Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; border-bottom: 1px solid #ddd; }
        th { background: #007bff; color: white; text-align: left; }
        tr:hover { background: #f1f1f1; }
        input[type="text"] { width: 180px; padding: 5px; font-family: monospace; }
        input[type="checkbox"] { transform: scale(1.2); }
        button { margin-top: 20px; padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; font-size: 16px; }
        button:hover { background: #218838; cursor: pointer; }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.html"><i class="bi bi-briefcase me-2"></i>Job Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="jobs.html">Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="cron_settings.php">Setting</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- End Navigation Bar -->
    <h1>Manage Cron Jobs</h1>
    <form method="post">
        <table>
            <tr>
                <th>Enable</th>
                <th>Job Name</th>
                <th>Schedule<br><small>(min hour dom mon dow)</small></th>
            </tr>
            <?php foreach ($cronJobs as $key => $job): ?>
                <tr>
                    <td><input type="checkbox" name="enable_<?= $key ?>" <?= isset($existingJobs[$key]) ? 'checked' : '' ?>></td>
                    <td><?= htmlspecialchars($job['label']) ?></td>
                    <td><input type="text" name="schedule_<?= $key ?>" value="<?= isset($existingJobs[$key]) ? $existingJobs[$key] : $job['default'] ?>"></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <button type="submit">Save Cron Jobs</button>
    </form>
</body>
</html>
