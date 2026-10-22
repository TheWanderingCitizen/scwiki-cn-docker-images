<?php
// Disposable SMTP sink: receives one message without contacting any mail service.
$server = stream_socket_server('tcp://127.0.0.1:2525', $errno, $error);
if (!$server) { throw new RuntimeException($error); }
echo "READY\n";
flush();
$client = stream_socket_accept($server, 15);
if (!$client) { exit(1); }
stream_set_timeout($client, 15);
fwrite($client, "220 localhost ESMTP\r\n");
while (($line = fgets($client)) !== false) {
    if (str_starts_with($line, 'DATA')) {
        fwrite($client, "354 End with dot\r\n");
        $message = '';
        while (($line = fgets($client)) !== false && $line !== ".\r\n") { $message .= $line; }
        fwrite($client, "250 Accepted\r\n");
        echo json_encode(['message' => $message]);
        fclose($client);
        fclose($server);
        exit(0);
    }
    fwrite($client, "250 localhost\r\n");
}
exit(1);
