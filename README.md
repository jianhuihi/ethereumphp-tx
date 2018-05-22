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
$from = Ethereum\Types\Buffer::hex('34c76cfdb9ed59f4e1e4e61fa27f97f90412d81b');
$to = Ethereum\Types\Buffer::hex('744d70fdbe2ba4cf95131626614a1763df805b9e');

$nonce = Ethereum\Types\Buffer::hex('2e');
$value = Ethereum\Types\Buffer::int(0);
$data = Ethereum\Types\Buffer::hex('095ea7b300000000000000000000000039a23012c065e0a93a6e268717c8b0f25f0430e90000000000000000000000000000000000000000000000000000000000000000');
$gasPrice = Ethereum\Types\Buffer::hex('033428f000');
var_dump($gasPrice);
$gasLimit = Ethereum\Types\Buffer::hex('02350c');
//$pk = Ethereum\Types\Buffer::hex('Set private key in PHP_ETH_RAW_TX_PK env var');
if (!$pk) {
    exit("/!\ Set private key in PHP_ETH_RAW_TX_PK env var" . PHP_EOL);
}

$chainId = Ethereum\Types\Buffer::int(1); // rinkeby
var_dump($chainId);
$tx = new Ethereum\Transaction(
    $from,
    $to,
    $value,
    $data,
    $nonce,
    $gasPrice,
    $gasLimit
);


```

Sign a transaction:
```php
$raw = $tx->getRaw($pk, $chainId);

echo "Generated raw transaction :" . PHP_EOL;
echo $raw->getHex() . PHP_EOL;
```

# License
MIT


