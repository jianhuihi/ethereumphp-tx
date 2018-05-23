<?php

/**
 * This file is part of ethereum-tx package.
 *
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 *
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace Ethereum;

use Ethereum\Crypto\Keccak;
use Ethereum\Rlp\RlpEncoder;
use Ethereum\Types\Buffer;
use Ethereum\Types\Byte;
use Ethereum\Types\Uint;

class Transaction {
	/**
	 * @var Buffer $nonce
	 */
	protected $nonce;

	/**
	 * @var Buffer $gasPrice
	 */
	protected $gasPrice;

	/**
	 * @var Buffer $gasLimit
	 */
	protected $gasLimit;

	/**
	 * @var String $to
	 */
	protected $to;

	/**
	 * @var Buffer $value
	 */
	protected $value;

	/**
	 * @var String $data
	 */
	protected $data;

	/**
	 * @var String|null $v
	 */
	protected $v;

	/**
	 * @var String|null $r
	 */
	protected $r;

	/**
	 * @var String|null $s
	 */
	protected $s;

	/**
	 * @var int
	 */
	protected $chainId;

	/**
	 * @var int
	 */
	protected $chainIdMul;

	/**
	 * Transaction constructor.
	 * @param Buffer|null $from
	 * @param Buffer|null $to
	 * @param Buffer|null $value
	 * @param Buffer|null $data
	 * @param Buffer|null $nonce
	 * @param Buffer|null $gasPrice
	 * @param Buffer|null $gasLimit
	 */
	public function __construct(Buffer $from = null, Buffer $to = null, Buffer $value = null, Buffer $data = null, Buffer $nonce = null, Buffer $gasPrice = null, Buffer $gasLimit = null) {

		$this->nonce = null === $nonce ? Buffer::int('1') : $nonce;
		$this->gasPrice = null === $gasPrice ? Buffer::int('10000000000000') : $gasPrice;
		$this->gasLimit = null === $gasLimit ? Buffer::int('196608') : $gasLimit;
		$this->from = $from ?? new Buffer();
		$this->to = $to ?? new Buffer();
		$this->value = null === $value ? Buffer::int('0') : $value;
		$this->data = $data ?? new Buffer();
	}

	/**
	 * @return array
	 */
	public function getInput(): array
	{
		return [
			"nonce" => $this->nonce,
			"gasPrice" => $this->gasPrice,
			"gasLimit" => $this->gasLimit,
			"to" => $this->to,
			"value" => $this->value,
			"data" => $this->data,
		];
	}

	/**
	 * @param Transaction $transaction
	 * @param Byte $privateKey
	 * @return Buffer
	 * @throws Exception
	 */
	public function sign(Buffer $privateKey, Buffer $chainId): Buffer{
		$chainId = $chainId ?? Buffer::int('1');
		$this->chainId = $chainId;

		$this->chainIdMul = Buffer::int($chainId->getInt() * 2);

		$hash = $this->hash($chainId);

		$signature = $this->signature($hash, $privateKey);

		$this->r = $signature->slice(0, 32)->getBuffer();
		$this->s = $signature->slice(32, 32)->getBuffer();
		$recoveryId = $signature->slice(64)->getInt();
		if ($this->chainId->getInt() > 0) {
			$this->v = Buffer::int($recoveryId + 35 + $this->chainIdMul->getInt());
		} else {
			$this->v = Buffer::int($recoveryId + 27);
		}
		return $this->withSignature();
	}

	/**
	 * @param Transaction $transaction
	 * @return Byte
	 * @throws Exception
	 */
	protected function hash(Buffer $chainId): Byte{
		/** @var array $raw */
		$raw = $this->getInput();
		if ($chainId->getInt() > 0) {
			$raw['v'] = $chainId;
			$raw['r'] = Buffer::int('0');
			$raw['s'] = Buffer::int('0');
		} else {
			unset($raw['v']);
			unset($raw['r']);
			unset($raw['s']);
		}
		/** @var Buffer $hash */
		$hash = RlpEncoder::encode($raw);
		//$hash = Rlp::encode($raw);
		/** @var Buffer $shaed */
		$shaed = Keccak::hash($hash->getBinary(), 256, true);
		return Byte::init($shaed);
	}

	/**
	 * @param Buffer $hash
	 * @param Buffer $privateKey
	 * @return Buffer
	 * @throws Exception
	 */
	public function signature(Byte $hash, Buffer $privateKey): Byte{
		/** @var resource $context */
		$context = secp256k1_context_create(SECP256K1_CONTEXT_SIGN | SECP256K1_CONTEXT_VERIFY);
		if (strlen($privateKey->getHex()) != 64) {
			throw new Exception("Incorrect private key");
		}
		/** @var resource $signature */
		$signature = '';
		if (secp256k1_ecdsa_sign_recoverable($context, $signature, $hash->getBinary(), $privateKey->getBinary()) != 1) {
			throw new Exception("Failed to create signature");
		}
		/** @var string $serialized */
		$serialized = '';
		$recoveryId = 0;
		secp256k1_ecdsa_recoverable_signature_serialize_compact($context, $signature, $serialized, $recoveryId);

		unset($context, $signature);

		return Byte::init($serialized . Uint::init($recoveryId)->getBinary());
	}

	/**
	 * @param Byte $hash
	 * @param Byte $signature
	 * @return Byte
	 * @throws Exception
	 */
	public static function recoverPublicKey(Byte $hash, Byte $signature): Byte{
		/** @var resource $context */
		$context = secp256k1_context_create(SECP256K1_CONTEXT_SIGN | SECP256K1_CONTEXT_VERIFY);
		/** @var resource $secpSignature */
		$secpSignature = '';
		$recoveryId = $signature->slice(64)->getInt();
		secp256k1_ecdsa_recoverable_signature_parse_compact($context, $secpSignature, $signature->slice(0, 64)->getBinary(), $recoveryId);
		/** @var resource $secpPublicKey */
		$secpPublicKey = '';
		secp256k1_ecdsa_recover($context, $secpPublicKey, $secpSignature, $hash->getBinary());
		$publicKey = '';
		secp256k1_ec_pubkey_serialize($context, $publicKey, $secpPublicKey, 0);
		unset($context, $secpSignature, $secpPublicKey);
		return Byte::init($publicKey);
	}
	/**
	 * @param Byte $publicKey
	 * @return Address
	 * @throws Exception
	 */
	public static function publicKeyToAddress(Byte $publicKey): Address{
		$ret = Byte::initWithHex(Keccak::hash($publicKey->slice(1)->getBinary()));
		return Address::initWithBuffer($ret->slice(12)->getBuffer());
	}

	/**
	 * @return Byte
	 */
	protected function withSignature(): Buffer{
		/** @var array $raw */
		$raw = $this->getInput();

		$raw = array_merge($raw, [
			$this->v,
			$this->r,
			$this->s,
		]);
		return RlpEncoder::encode($raw);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function __set(string $name, $value) {
		$this->{$name} = $value;
		return $this;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get(string $name) {
		return $this->{$name};
	}

}
