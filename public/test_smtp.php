<?php

$host = 'ssl://smtp.hostinger.com'; 
$port = 465;
$timeout = 30; 

$errno = null;
$errstr = null;

echo "Tentative de connexion à $host:$port...\n";

// Essayer d'ouvrir un socket sécurisé
$socket = @stream_socket_client(
    "$host:$port",
    $errno,
    $errstr,
    $timeout,
    STREAM_CLIENT_CONNECT,
    stream_context_create([
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'cafile' => 'C:\\laragon\\etc\\ssl\\cacert.pem',
            'allow_self_signed' => false,
            'capture_peer_cert' => true,
            'capture_peer_cert_chain' => true, 
        ],
    ])
);

if (!$socket) {
    echo "Échec de la connexion : ($errno) $errstr\n";
    if (isset($socket) && $socket) { 
        $params = stream_get_meta_data($socket);
        if (isset($params['peer_certificate']) && $params['peer_certificate']) {
            echo "Certificat du pair capturé.\n";
            print_r(openssl_x509_parse($params['peer_certificate']));
        }
        if (isset($params['peer_certificate_chain']) && $params['peer_certificate_chain']) {
            echo "Chaîne de certificats du pair capturée.\n";
            foreach ($params['peer_certificate_chain'] as $cert) {
                print_r(openssl_x509_parse($cert));
            }
        }
    }
} else {
    echo "Connexion établie avec succès !\n";
    // Lire le message d'accueil SMTP initial
    $response = fgets($socket, 1024);
    echo "Le serveur dit : $response\n";

    fwrite($socket, "EHLO yourdomain.com\r\n");
    $response = fgets($socket, 1024);
    echo "Réponse EHLO : $response\n";

    fclose($socket);
    echo "Connexion fermée.\n";
}

?>