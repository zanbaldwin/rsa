<?php

/**
 * RSA Library
 *
 * A PHP implementation of the RSA public-private key encryption and digital signing, including generation of new
 * keypairs.
 * This class uses the Binary Calculator functions in PHP due to the possibility of the primes being so large that PHP
 * internals not being able to cope.
 *
 * @category	Libraries
 * @package		Cryptography
 * @subpackage	RSA
 */

	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'library.php';

	class RSA extends library {
		protected $theirs	= false,
				  $yours	= false;

		/**
		 * Constructor function
		 * Doesn't do anything, we don't have any of the required keys yet.
		 */
		protected function __construct() {
			// Setup.
		}

		/**
		 * Generate Keypair
		 *
		 * Generate an RSA keypair from two prime numbers that are provided. The numbers given are not checked other
		 * than that they are integers. It is up to the user to make sure the integers passes are prime, are not the
		 * same, and are of the same bit-length.
		 *
		 * @access public
		 * @param string $prime_one
		 * @param string $prime_two
		 * @return object
		 */
		public function generate($prime_one, $prime_two) {
			if(
				!is_string($prime_one)
				|| !is_string($prime_two)
				|| !preg_match('/^[0-9]+$/', $prime_one)
				|| !preg_match('/^[0-9]+$/', $prime_two)
			) {
				return false;
			}
			// Start generating key pair.
			// Find the modulus of the two primes.
			$modulus = bcmul($prime_one, $prime_two);
			// Find the modulus of one minus each prime.
			$calcbit = bcmul(
				bcsub($prime_one, 1),
				bcsub($prime_two, 1)
			);
			// Find the coprime. This is the public key.
			$public = $this->coprime($calcbit);
			// Find the private key by using the extended Euclid algorithm.
			$private = $this->extend($public, $calcbit);
			// Return the keypair, and its modulus in as a key object.
			return $this->key($public, $private, $modulus);
		}

		/**
		 * Greatest Common Divisor
		 *
		 * @access protected
		 * @param string $e
		 * @param string $modulus
		 * @return string
		 */
		protected function gcd($e, $modulus) {
			$a = $e;
			$b = $modulus;
			while(bccomp($a, 0) != 0) {
				// Modulus fraction.
				$c = bcsub(
					$b,
					bcmul(
						$a,
						bcdiv($b, $a, 0)
					)
				);
				$b = $a;
				$a = $c;
			}
			return $b;
		}

		/**
		 * Calculate Co-prime
		 *
		 * The following conditions are assumed:
		 * "GCD($modulus,$public)=1" AND "1<$public<$modulus".
		 *
		 * @access protected
		 * @param string $calcbit
		 * @return string(integer)
		 */
		protected function coprime($calcbit) {
			$e = '3';
			if(bccomp($this->gcd($e, $calcbit), '1') != 0) {
				$e = '5';
				$step = '2';
				while(bccomp($this->gcd($e, $calcbit), '1') != 0) {
					$e = bcadd($e, $step);
					if($step === '2') {
						$step = '4';
					}
					else {
						$step = '2';
					}
				}
			}
			return $e;
		}

		/**
		 * Calculate private key.
		 *
		 * @access protected
		 * @param string $public
		 * @param string $calcbit
		 * @return string(integer)
		 */
		protected function extend($public, $calcbit) {
			$u = array('1', '0', $calcbit);
			$v = array('0', '1', $public);
			while(bccomp($v[2], 0) != 0) {
        		$q = bcdiv($u[2], $v[2], 0);
        		$t = array(
	        		bcsub($u[0], bcmul($q, $v[0])),
	        		bcsub($u[1], bcmul($q, $v[1])),
	        		bcsub($u[2], bcmul($q, $v[2])),
	        	);
        		$u[0] = $v[0];
        		$u[1] = $v[1];
        		$u[2] = $v[2];
        		$v[0] = $t[0];
        		$v[1] = $t[1];
        		$v[2] = $t[2];
        		$z  = '1';
			}
			$uu = $u[0];
			$vv = $u[1];
			return bccomp($vv, 0) == -1
				? bcadd($vv, $calcbit)
				: $vv;
		}

		/**
		 * Load Keys
		 *
		 * This function loads a keypair belonging to an external entity and your keypair object.
		 *
		 * @access public
		 * @param array(integer, integer)
		 * @param array(integer, integer)
		 * @return void
		 */
		public function load($yours, $theirs = false) {
			if(!is_object($theirs) || !is_object($yours)) {
				return false;
			}
			// Set your keypair.
			if(
				isset($yours->public)
				&& isset($yours->private)
				&& isset($yours->modulus)
			) {
				$this->yours = $this->key(
					$yours->public,
					$yours->private,
					$yours->modulus
				);
			}
			// Set their keypair. Normally, you won't have their private key.
			if(
				isset($theirs->public)
				&& isset($theirs->modulus)
			) {
				$this->theirs = $this->key(
					$theirs->public,
					isset($theirs->private) ? $theirs->private : false,
					$theirs->modulus
				);
			}

		}

		/**
		 * Create Key Object
		 *
		 * Takes the public, private and modulus and returns a key object with those values to be used in other methods
		 * of this library.
		 *
		 * @access public
		 * @param string $public
		 * @param string $private
		 * @param string $modulus
		 * @return object|false
		 */
		public function key($public, $private, $modulus) {
			if(
				!is_string($public)
				|| !(is_string($private) || !$private)
				|| !is_string($modulus)
				|| !preg_match('/^[0-9]+$/', $public)
				|| !(preg_match('/^[0-9]+$/', $private) || !$private)
				|| !preg_match('/^[0-9]+$/', $modulus)
			) {
				return false;
			}
			$key = array(
				'public'	=> $public,
				'private'	=> $private ? $private : false,
				'modulus'	=> $modulus,
			);
			return (object) $key;
		}

		/**
		 * Encrypt
		 *
		 * Encrypt a message with THEIR public key.
		 * If $theirs is false, then encrypt the message with YOUR private key (for digital signing).
		 *
		 * @access public
		 * @param string $message
		 * @param integer $step
		 * @return string
		 */
		public function encrypt($message, $theirs = true, $step = 3) {
			if(
				($theirs && (!isset($this->theirs->public) || !isset($this->theirs->modulus)))
				||
				(!$theirs && (!isset($this->yours->private) || !isset($this->yours->modulus)))
			) {
				return false;
			}
			$modulus	= $theirs
				? $this->theirs->modulus
				: $this->yours->modulus;
			$key		= $theirs
				? $this->theirs->public
				: $this->yours->private;
			$coded		= '';
			$max		= strlen($message);
			$packets	= ceil($max / $step);
			for($i = 0; $i < $packets; $i++) {
				$packet = substr($message, $i * $step, $step);
				$code = '0';
				for($j = 0; $j < $step; $j++) {
					$code = bcadd(
						$code,
						bcmul(
							ord($packet[$j]),
							bcpow('256', $j)
						)
					);
				}
				$code	= bcpowmod($code, $key, $modulus);
				$coded	.= $code . ' ';
			}
			return trim($coded);
		}

		/**
		 * Decrypt
		 *
		 * Decrypt a message with YOUR private key.
		 * If $yours is false, then decrypt the message with THEIR public key (for digital signature authentication).
		 *
		 * @access public
		 * @param string $message
		 * @return string
		 */
		public function decrypt($message, $yours = true) {
			if(
				($yours && (!isset($this->yours->modulus) || !isset($this->yours->private)))
				||
				(!$yours && (!isset($this->theirs->public) || !isset($this->theirs->modulus)))
			) {
				return false;
			}
			$modulus	= $yours
				? $this->yours->modulus
				: $this->theirs->modulus;
			$key		= $yours
				? $this->yours->private
				: $this->theirs->public;
			$coded		= explode(' ', $message);
			$message	= '';
			foreach($coded as $code) {
				$code = bcpowmod($code, $this->yours->private, $this->yours->modulus);
				while(bccomp($code, '0') != 0) {
					$ascii		= bcmod($code, '256');
					$code		= bcdiv($code, '256', 0);
					$message	.= chr($ascii);
				}
			}
			return $message;
		}

		/**
		 * Sign Message
		 *
		 * Sign a message's digest with your private key, to authenticate that you are the owner of the original message.
		 *
		 * @access public
		 * @param string $message
		 * @return string
		 */
		public function sign($message) {
			return $this->encrypt(md5($message));
		}

		/**
		 * Prove Message
		 *
		 * Authenticate that a message's signature came from the owner of the private key, corresponding to the public
		 * you possess.
		 *
		 * @access public
		 * @param string $message
		 * @param string $signature
		 * @return boolean
		 */
		public function prove($message, $signature) {
			// Decrypt the signature with their public key.
			$digest = $this->decrypt($signature, false);
			return $digest == md5($message);
		}

	}

////////////////////////////////////////////////////////////////////////////////

	$rsa = RSA::getInstance();
	// Generate a keypair from two random prime numbers.
	$keypair = $rsa->generate('235897301', '235898237');

	// For testing purposes, we are going to define a dummy entities keypair here.
	// Key ( Public, Private, Modulus ).
	$theirs = $rsa->key('11', false, '55646646815960129');

	// Load both keypairs into the library.
	$rsa->load($keypair, $theirs);

	echo "\n";
	echo "Our keypair:\n";
	var_dump($keypair);
	echo "Their keypair:\n";
	var_dump($theirs);
	echo "\n";
	$message = "Hi THEM!\nI like pizza. Pizza is yummy. Hurray for pizza!\nZander.";
	$coded = $rsa->encrypt($message);
	var_dump($rsa->sign($coded));