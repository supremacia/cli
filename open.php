<?php
//Gerador de certificados e chaves pública e privada.


$fncert = CONFIG_KEYS_PATH.'certificate.crt';
$fnsscert = CONFIG_KEYS_PATH.'self_signed_certificate.cer';
$fnprivate = CONFIG_KEYS_PATH.'private.key';
$fnpublic = CONFIG_KEYS_PATH.'public.key';

$SSLcnf = [];
$dn = [];

//get configurations
include CONFIG_KEYS_PATH.'openssl.php';

// Generate a new private (and public) key pair
$privkey = openssl_pkey_new($SSLcnf);

// Generate a certificate signing request
$csr = openssl_csr_new($dn, $privkey, $SSLcnf);

// You will usually want to create a self-signed certificate at this
// point until your CA fulfills your request.
// This creates a self-signed cert that is valid for 365 days
$sscert = openssl_csr_sign($csr, null, $privkey, 365, $SSLcnf);

// Now you will want to preserve your private key, CSR and self-signed
// cert so that they can be installed into your web server, mail server
// or mail client (depending on the intended use of the certificate).
// This example shows how to get those things into variables, but you
// can also store them directly into files.
// Typically, you will send the CSR on to your CA who will then issue
// you with the "real" certificate.

//CERTIFICADO
openssl_csr_export_to_file($csr, $fncert, false);

//CERTIFICADO AUTO-ASSINADO
openssl_x509_export_to_file($sscert, $fnsscert, false);

//CHAVE PRIVADA (private.pem)
openssl_pkey_export_to_file($privkey , $fnprivate, null, $SSLcnf);

//CHAVE PÚBLICA (public.key)
file_put_contents($fnpublic, openssl_pkey_get_details($privkey)['key']);

/**
 * @todo Criar rotinas de interceptação de erros
 *
 */
// Show any errors that occurred here
//while (($e = openssl_error_string()) !== false) {
//    echo "\n".$e."\n";
//}
