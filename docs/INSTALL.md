# Installation

## Requirements

You'll need a PEAR installation on your system.

### Debian/Ubuntu

    apt-get install php-pear

### FreeBSD

    cd /usr/ports/devel/pear && make install clean

### Other OS'?

See the [PEAR manual][manual].

[manual]: http://pear.php.net/manual/en/installation.php

## Install TillStore

### From Github:

    git clone git://github.com/till/TillStore.git
    cd TillStore
    pear install -f package.xml

Or:

    pear install -f http://cloud.github.com/downloads/till/TillStore/TillStore-0.1.0.tgz

### From my PEAR channel (soon!)

    pear channel-discover pear.lagged.de
    pear install lagged/TillStore

### What does it install?

The location depends on your system, and PEAR configuration.

*Libraries*

 * /usr/share/php/TillStore.php
 * /usr/share/php/TillStore/Exception.php
 * /usr/share/php/TillStore/Server.php

*Executable*
 
 * /usr/bin/TillStore

*Configuration*

 * /usr/share/php/data/TillStore/etc/default.ini

(Try `pear list-files lagged/TillStore` to verify.)
