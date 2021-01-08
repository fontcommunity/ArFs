
<?php

include __DIR__ . '/vendor/autoload.php';




function _ar_upload_file($filename, $tags) {
  if(file_exists($filename) && filesize($filename)) {
    $config = _ar_get_config();
    $arweave = new \Arweave\SDK\Arweave($config['protocol'], $config['ip'], $config['port']);
    $jwk = json_decode(file_get_contents($config['wallet_file']), true);
    $wallet =  new \Arweave\SDK\Support\Wallet($jwk);

    $fileData = file_get_contents($filename);
    $txnData = [
      'tags' => $tags,
      'data' => $fileData,
    ];

    $transaction = $arweave->createTransaction($wallet, $txnData);
    $arweave->commit($transaction);
    return $transaction;
  }
  return false;
}


function _ar_get_config(){
  return [
    'protocol' => 'http',
    'ip' => '209.97.142.169',
    'port' => 1984,
    'wallet_file' => 'jwk.json',
  ];
}