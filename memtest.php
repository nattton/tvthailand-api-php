<?
    $memcache = new Memcache;
    $memcache->connect('localhost', 11211) or die ("Could not connect");
    $tmp = (object) array(
        'a' => 'asdf',
        'b' => 564,
        'c' => 59.2,
        'd' => array(0, true, null, 'h'),
    );
    $memcache->set('key', $tmp) or die ("Failed to memcache it");
    echo var_dump($memcache->get('key'));