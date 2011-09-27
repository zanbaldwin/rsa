RSA Implementation
==================

Cryptography is really beginning to intrigue me, so this library will be my implemention of the [RSA][2] public key standard in [PHP][3]. Please note that this is *not* the same public-private keys generated for [SSH][1], as [GitHub][4] uses.

The keys generated from this library are _integers_.

Example: Sending
----------------

First of all, you need to generate a keypair:

	$rsa = RSA::getInstance();
	$keypair = $rsa->generate('235897301', '235898237');

	// This will produce:
	//
	// object(stdClass)#2 {
	//   ["public"]=>
	//   string(1) "3"
	//   ["private"]=>
	//   string(17) "37098504631441867"
	//   ["modulus"]=>
	//   string(17) "55647757418958337"
	// }

Now we have our keypair, save it somewhere to use each time you wish to use this library.

Next we need to load our keypair, and the entity (to be referred to as "their" keypair) in which we are speaking to, into the library. We should only know the public and modulus of their keypair. From now on, `$keypair`, as defined in the previous example, shall now be referred to as `$ours`.

	// These are the details of their public key, which they have given to us.
	$their = $rsa->key('11', false, '55646646815960129');
	$rsa->load($ours, $theirs);

Now, we want to encrypt a message, with their public key, so that only they can read it.

	$message = "Hi THEM!\nI like pizza. Pizza is yummy. Hurray for pizza!\nZander.";
	$code = $rsa->encrypt($message);

	// The value of $code is now:
	// string(392) "32458514235257846 51299909426313176 32956740648000718 17459816184351885 4004928228386697 24386756643461956 32544687418882645 49356138603861559 22101840479782611 24606219391583867 25028129780292935 32977922840902810 47954912328391409 41262476071722365 50036998489795733 5442965523873255 27964722027452886 22101840479782611 46030104490481892 18495249773597582 55365469161095793 3721745649117981"

Right, now, only their private key may decrypt that message. So we can send that too them. But they can't guarantee that we were the ones who sent it; we can digitally sign it with our private key (which we are the only entity with a copy), so they can verify with our public key (which they have) and know we sent it.

Now, I although this works completely, I don't know the industry standard on whether you sign the original message, or the encrypted message. Google hasn't been that helpful on the matter. I am going to go for the encrypted message, as the original may contain a virus if sent from hacker. They will want to verify we sent it before decrypting and opening it.

	$signature = $rsa->sign($coded);
	// $signature now contains the value:
	// string(195) "19156294486951094 53452804044120888 10127989428253415 7298903737809832 20710667286475213 42451771418843995 47662224257293058 45760969856535614 5306920628062091 7026112103950355 33380858535257306"

Send both of these values to the external entity and they can, with their private key, and our public key, verify the signature and decrypt the message.

[1]: http://en.wikipedia.org/wiki/Secure_Shell "SSH: Secure Shell"
[2]: http://en.wikipedia.org/wiki/RSA "RSA Algorithm on WikiPedia"
[3]: http://php.net/ "PHP: Hypertext Preprocessor"
[4]: https://github.com/ "GitHub"