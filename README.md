# TillStore -- Because key-value-stores are awesome!

Enough said. ;-) Well, maybe not.

## The History

Please see docs/HISTORY.md.

## Installation

Please see docs/INSTALL.md.

## Usage

By default TillStore starts on `localhost:31337`. You may override these settings in a local.ini.

 * Start TillStore: `/usr/bin/TillStore`
 * Curl examples:

    * Command: `curl -X GET http://localhost:31337/foo`
    * Response: `Not found.`

    * Command: `curl -X POST -d bar http://localhost:31337/foo`
    * Response: `OK`

    * Command: `curl -X GET http://localhost:31337/foo`
    * Response: `bar`

 (In a series, of course.)

 * Shutdown the server (_special_ administrator feature):

    `telnet localhost 31337`

    `SHUTDOWN`

# What ...

## ... TillStore is.

 * fun
 * unit-tested (the TillStore, not TillStore_Server)

## ... TillStore is not.

 * persistent
 * fully HTTP-compatible
 * production-ready :-)