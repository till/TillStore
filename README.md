# TillStore -- Because key-value-stores are awesome!

Enough said. ;-) Well, maybe not.

## The History

I went to nosqlberlin on Thursday, October 22nd, 2009. It was a pretty cool event and
the talks were people presenting their database. Among those "new database" Redis,
CouchDB, Riak and MongoDB (all pretty cool and _awesome_ projects!).

Previously, I had joked with a friend about my own key-value-store -- TillStore, but
I had actually never ever implemented it.

When I got home from nosqlberlin on Thursday night, I realized I had lost the keys to
my apartment. So even though they were found and one of my (most awesome) neighbours
had deposited them at the bakery downstairs, I wish shit out of luck. The backery was
closed at 10 PM.

So instead of freezing my butt off outside, I went to the next Internet cafe (across
the street from where I live), did some work, and started hacking on TillStore. So
not even 10 work hours later, this is the initial 0.1.0-alpha release (BE GENTLE).

People may say, "WTF?!". Yeah, correct. I wrote a key-value-store in PHP. But I did
not actually write it to power Yahoo!, I wrote it as a proof of concept, and because
I can.

Deal with. And enjoy!

## Installation

You'll need a PEAR installation on your system.

### Ubuntu

    apt-get install php-pear

### FreeBSD

    cd /usr/ports/devel/php-pear && make install clean

### Install TillStore

    pear channel-discover pear.lagged.de
    pear install lagged/TillStore

Depending on your system, this sets up TillStore in /usr/bin/TillStore and libraries in:

 * /usr/share/php/TillStore.php
 * /usr/share/php/TillStore/Exception.php
 * /usr/share/php/TillStore/Server.php

(Try `pear list-files lagged/TillStore` to verify.)

## Usage

By default TillStore starts on `localhost:31337`. You may override these settings in a local.ini.

 * Start TillStore: /usr/bin/TillStore (&)
 * Curl examples:

    till@home:~/$ curl -X GET http://localhost:31337/foo
    Not found.
    till@home:~/$ curl -X POST -d bar http://localhost:31337/foo
    OK
    till@home:~/$ curl -X GET http://localhost:31337/foo
    bar

 * Shutdown the server:

    telnet localhost 31337
    SHUTDOWN

# What ...

## ... TillStore is.

 * fun
 * unit-tested (the TillStore, not TillStore_Server)

## ... TillStore is not.

 * persistent
 * fully HTTP-compatible
 * production-ready :-)