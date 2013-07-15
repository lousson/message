Lousson: Message
================

[![Build Status](https://travis-ci.org/lousson/message.png?branch=master)]
(https://travis-ci.org/lousson/message)

NOTE: THIS PACKAGE IS A WORKING DRAFT !!

The `Lousson_Message` package provides various facilities to communicate
between processes, network nodes and applications in both, synchroneous
and asynchroneous fashion.


Dependencies
------------

The `Lousson_Message` package itself depends on PHP and a(ny) PSR-0
compatible autoload implementation:

- **PHP 5.3.0+**:                           http://www.php.net/
- **PSR-0 Autoloader**:                     http://pear.phix-project.org/
- **Lousson_Exception 0.2.2+**:             http://pear.lousson.org/
- **Lousson_Record 0.4.0+**:                http://pear.lousson.org/
- **Lousson_URI 0.4.2+**:                   http://pear.lousson.org/

However, there is also a bunch of tools the development and build
processes rely on, e.g.:

- **Git 1.7+**:                             http://www.git-scm.com/
- **Phing 2.4+**:                           http://www.phing.info/
- **Phix 0.15.0+**:                         http://www.phix-project.org/
- **PHPUnit 3.7+**:                         http://www.phpunit.de/
- **Pirum 1.1.4+**:                         http://pirum.sensiolabs.org/

Please note that The Lousson Project does NOT provide support for any of
the aforementioned software!


Resources
---------

The Lousson packages are available through the PEAR channel at
http://pear.lousson.org - thus, one can use the "pear" script to
install any of them, e.g.:

	pear channel-discover pear.lousson.org
	pear install lousson/Lousson_Message

The complete sourcecode and version history is avialabe at GitHub.
One may either visit http://github.com/lousson/message or clone
the source tree directly:

	git clone git@github.com:lousson/message.git

GitHub is also used to track issues like bugs and feature-requests:

	http://github.com/lousson/message/issues

Pull requests and other contributions are welcome!


Copyright & License
-------------------

Unless denoted otherwise, the following terms apply to all software
provided within the `Lousson_Message` package:

	Copyright (c) 2013, The Lousson Project

	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions
	are met:

	1) Redistributions of source code must retain the above copyright
	   notice, this list of conditions and the following disclaimer.
	2) Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in
	   the documentation and/or other materials provided with the
	   distribution.

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
	"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
	LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
	FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
	INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
	SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
	HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
	STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
	OF THE POSSIBILITY OF SUCH DAMAGE.

Please note that the creators of the software mentioned in the
"Dependencies" section define their own licensing terms & conditions!

