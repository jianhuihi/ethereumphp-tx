# ethereum-tx
[![Build Status](https://travis-ci.org/web3p/ethereum-tx.svg?branch=master)](https://travis-ci.org/web3p/ethereum-tx)
[![codecov](https://codecov.io/gh/web3p/ethereum-tx/branch/master/graph/badge.svg)](https://codecov.io/gh/web3p/ethereum-tx)

Ethereum transaction library in PHP.

# 安装

```
composer require jianhuihi/ethereum-tx
```


## 依赖

```
ext-gmp: ^7.2
ext-scrypt: ^1.4
ext-secp256k1: ^0.1.0
ext-keccak: ^0.2
bitwasp/buffertools: ^0.5.0
```

* ext-scrypt: [https://github.com/DomBlack/php-scrypt](https://github.com/DomBlack/php-scrypt)
* ext-secp256k1: [https://github.com/Bit-Wasp/secp256k1-php](https://github.com/Bit-Wasp/secp256k1-php)
* ext-keccak: [https://github.com/EricYChu/php-keccak-hash](https://github.com/EricYChu/php-keccak-hash)


# 使用

Create a transaction:
```php
use Ethereum\Transaction;

// without chainId
$transaction = new Transaction([
    'nonce' => '0x01',
    'from' => '0xb60e8dd61c5d32be8058bb8eb970870f07233155',
    'to' => '0xd46e8dd67c5d32be8058bb8eb970870f07244567',
    'gas' => '0x76c0',
    'gasPrice' => '0x9184e72a000',
    'value' => '0x9184e72a',
    'data' => '0xd46e8dd67c5d32be8d46e8dd67c5d32be8058bb8eb970870f072445675058bb8eb970870f072445675'
]);

// with chainId
$transaction = new Transaction([
    'nonce' => '0x01',
    'from' => '0xb60e8dd61c5d32be8058bb8eb970870f07233155',
    'to' => '0xd46e8dd67c5d32be8058bb8eb970870f07244567',
    'gas' => '0x76c0',
    'gasPrice' => '0x9184e72a000',
    'value' => '0x9184e72a',
    'chainId' => 1,
    'data' => '0xd46e8dd67c5d32be8d46e8dd67c5d32be8058bb8eb970870f072445675058bb8eb970870f072445675'
]);
```

Sign a transaction:
```php
use Ethereum\Transaction;

$signedTransaction = $transaction->sign('your private key');
```

# License
MIT


