RSA Implementation
==================

Cryptography is really beginning to intrigue me, so this library will be my implemention of the [RSA][2] public key standard in [PHP][3]. Please note that this is *not* the same public-private keys generated for [SSH][1], as [GitHub][4] uses.

The keys generated from this library are _integers_.

Example: Keypairs
-----------------

First of all, you need to generate a keypair, by providing the `generate()` method with two large prime numbers.
A file with some sample prime numbers has been included in this repository.

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

Example: Sending
----------------

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

Example: Receiving
------------------

Okay. Now, using the same keypairs for ourselves and the "external entity". Except this time we have received the following data:

	$message:
	string(466) "12155966171842900 26118154798396694 14250279235288236 50610241651959471 21808883696030938 54423392094670248 9870925205268418 8739790981591918 47084251473475750 32278194315425703 3692519925827522 40785317999691389 47296259483576909 44434771484384908 12379886597107548 12206526790309673 49401166903442937 7282538360766037 47397459272021711 22050690086818991 12396100921598324 1214479131795388 1525359502895831 4819051131036891 12632728583926439 36099544846488291 97336"
	$signature
	string(192) "25532648015616690 15241520630239213 15193110137771952 31283776609007542 22357473601781567 43396255023594588 35730975624270977 29817040025643614 1099355056013519 8312448325796817 16885582886375"

First, before we decrypt and open the message, we want to make sure it has been sent from THEM, and not someone else.

	$from_THEM = $rsa->prove($message, $signiture);
	// The value of $from_THEM is:
	bool(true)

Now we now it is from a trusted source, we can decrypt it.

	$original = $rsa->decrypt($message)
	// The value of $original is:
	string(79) "Hi Zander!\nI like pizza, too. Pizza is agreeably yummy. Hurray for pizza!\nTHEM."

There you have it! I will try to improve this. It's extremely basic, but up next is the [AES][5] algorthm and [DSA][6] for SSH keypairs :)

[1]: http://en.wikipedia.org/wiki/Secure_Shell "SSH: Secure Shell"
[2]: http://en.wikipedia.org/wiki/RSA "RSA Algorithm on WikiPedia"
[3]: http://php.net/ "PHP: Hypertext Preprocessor"
[4]: https://github.com/ "GitHub"
[5]: http://en.wikipedia.org/wiki/Advanced_Encryption_Standard "Advanced Encryption Standard on WikiPedia"
[6]: http://en.wikipedia.org/wiki/Digital_Signature_Algorithm "Digitial Signature Algorithm on WikiPedia"