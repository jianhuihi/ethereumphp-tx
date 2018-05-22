<?php

namespace Ethereum\Rlp;

use Ethereum\Types\Buffer;

/**
 * @see https://github.com/ethereum/wiki/wiki/RLP
 */
class RlpEncoder {

	public static function encode($input): Buffer {
		if ($input instanceof Buffer) {
			if ($input->getBinary() === Buffer::hex("00")->getBinary()) {
				return new Buffer(chr(128));
			}
			if (strlen($input->getBinary()) == 1 && ord($input->getBinary()) < 128) {
				return $input;
			}
			return new Buffer(self::encodeLength(strlen($input->getBinary()), 128) . $input->getBinary());
		}

		if (is_array($input)) {
			/** @var string $output */
			$output = '';
			foreach ($input as $item) {
				$encode = self::encode($item);
				$output .= $encode->getBinary();
			}

			return new Buffer(self::encodeLength(strlen($output), 192) . $output);
		}

		throw new \Exception('Invalid type: ' . gettype($input));
	}

	public static function encodeLength(int $l, int $offset): string {
		if ($l < 56) {
			return chr($l + $offset);
		}

		if ($l < 256 ** 8) {
			/** @var string $bl */
			$bl = Buffer::int($l)->getBinary();

			return chr(strlen($bl) + $offset + 55) . $bl;
		}

		throw new \Exception('Failed to encode length');
	}
}

namespace Ethereum;

use Ethereum\Types\Byte;
use Ethereum\Types\TypeInterface;
use Ethereum\Types\Uint;
use Exception;
use InvalidArgumentException;

class RlpEncoder {
	/**
	 * @param array|string|TypeInterface $input
	 * @return Byte
	 * @throws Exception
	 */
	public static function encode($input): Byte {
		if ($input instanceof TypeInterface) {
			if ($input->getBinary() === Byte::initWithHex('00')->getBinary()) {
				return Byte::init(chr(128));
			}
			if (strlen($input->getBinary()) == 1 && ord($input->getBinary()) < 128) {
				return Byte::initWithBuffer($input->getBuffer());
			}
			return Byte::init(static::encodeLength(strlen($input->getBinary()), 128) . $input->getBinary());
		}

		if (is_array($input)) {
			/** @var string $output */
			$output = '';
			foreach ($input as $key => $item) {
				$encode = static::encode($item);
				$output .= $encode->getBinary();
			}
			return Byte::init(static::encodeLength(strlen($output), 192) . $output);
		}

		throw new InvalidArgumentException('Argument must be an instance of Buffer or an array.');
	}

	/**
	 * @param int $length
	 * @param int $offset
	 * @return string
	 * @throws Exception
	 */
	public static function encodeLength(int $length, int $offset): string {
		if ($length < 56) {
			return chr($length + $offset);
		} elseif ($length < 256 ** 8) {
			/** @var string $bl */
			$bl = Uint::init($length)->getBinary();
			return chr(strlen($bl) + $offset + 55) . $bl;
		} else {
			throw new Exception('Failed to encode length.');
		}
	}
}
