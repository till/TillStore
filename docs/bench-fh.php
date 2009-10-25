#!/usr/bin/env php
<?php
/**
 * Benchmark file_put_contents() vs. a series of fopen(), fwrite()
 * and fclose() calls.
 *
 * Just run this with:
 *
 * time ./bench.php
 *
 * @category Database
 * @package  TillStore
 * @author   Till Klampaeckel <till@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.html BSD License
 * @version  SVN: $Id$
 * @link     http://github.com/till/TillStore
 */
$do = 100000;

$mask = '/dev/shm/bench-TillStore-%s';
while ($do > 0) {
    apc_file_put_contents(sprintf($mask, $do), "foobar");
    // unlink(sprintf($mask, $do));
    apc_delete(sprintf($mask, $do));
    --$do;
}

function TillStore_file_put_contents($filename, $value) {
    $fp = fopen($filename, 'w');
    fwrite($fp, $value);
    fclose($fp);
}

function apc_file_put_contents($filename, $value) {
    apc_store($filename, $value);
}