<?php
/**
 * Lightweight SMTP mailer (no external library required).
 * Settings are read from the `settings` DB table via setting().
 */
final class Mailer
{
    /** Send an HTML email. Returns true on success, false on failure. */
    public static function send(string $to, string $toName, string $subject, string $htmlBody): bool
    {
        $host     = setting('smtp_host');
        $port     = (int) setting('smtp_port', '587');
        $user     = setting('smtp_user');
        $pass     = setting('smtp_pass');
        $from     = setting('smtp_from');
        $fromName = setting('gym_name', 'Caato Gym');

        // Fallback to PHP mail() if SMTP not configured
        if (!$host || !$from) {
            $headers  = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$fromName} <noreply@caatogym.com>\r\n";
            return @mail($to, $subject, $htmlBody, $headers);
        }

        try {
            // Choose context for encryption
            $ctx     = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
            $scheme  = ($port === 465) ? 'ssl' : 'tcp';
            $socket  = @stream_socket_client("{$scheme}://{$host}:{$port}", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx);
            if (!$socket) throw new RuntimeException("Connect failed: $errstr");
            stream_set_timeout($socket, 10);

            $read = fn() => fgets($socket, 1024);
            $write = function (string $cmd) use ($socket) { fwrite($socket, $cmd . "\r\n"); };

            $read(); // 220 greeting
            $write("EHLO caatogym.local");
            while (($line = $read()) && strpos($line, ' ') !== false && substr($line, 3, 1) === '-') {}; // read all EHLO lines

            // STARTTLS for port 587
            if ($port === 587) {
                $write("STARTTLS");
                $read();
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $write("EHLO caatogym.local");
                while (($line = $read()) && strpos($line, ' ') !== false && substr($line, 3, 1) === '-') {};
            }

            // Auth
            if ($user && $pass) {
                $write("AUTH LOGIN");
                $read();
                $write(base64_encode($user));
                $read();
                $write(base64_encode($pass));
                $resp = $read();
                if (substr(trim($resp), 0, 3) !== '235') throw new RuntimeException("SMTP auth failed: $resp");
            }

            $write("MAIL FROM:<{$from}>");
            $read();
            $write("RCPT TO:<{$to}>");
            $resp = $read();
            if ((int) substr(trim($resp), 0, 3) >= 500) throw new RuntimeException("RCPT rejected: $resp");
            $write("DATA");
            $read();

            $boundary = md5(uniqid('', true));
            $msg  = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$from}>\r\n";
            $msg .= "To: =?UTF-8?B?" . base64_encode($toName) . "?= <{$to}>\r\n";
            $msg .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
            $msg .= "MIME-Version: 1.0\r\n";
            $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
            $msg .= "Content-Transfer-Encoding: base64\r\n";
            $msg .= "\r\n";
            $msg .= chunk_split(base64_encode($htmlBody));
            $msg .= "\r\n.";
            $write($msg);
            $read();

            $write("QUIT");
            fclose($socket);
            return true;
        } catch (Throwable $e) {
            @file_put_contents(ROOT . '/storage/error.log',
                date('c') . ' [Mailer] ' . $e->getMessage() . "\n", FILE_APPEND);
            return false;
        }
    }
}
