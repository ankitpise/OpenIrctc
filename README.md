# OpenIrctc - PHP API to check PNR & Train Schedule

Open Irctc is open PHP library for fetching realtime PNR Status & Railway Schedule with Train Number

  - Check PNR With PNR Number
  - Check Railways Timings With Train Number


### Version
1.0

### Tech

Requirements:

* [PHP-Curl] - PHP Library to make external HTTP request.

### Loading Library

```sh
include 'OpenIrctc.php';
$irctc = new OpenIrctc($pnr_number); // $pnr_number = null by default
```

### Checking PNR Status

```sh
$irctc->set_language(); // english / hindi. English by default
$irctc->pnr_full_check($pnr_number); // not necessary to pur $pnr number if initiated library with it.
```

### Checking Train Schedule

```sh
$irctc->set_language(); // english / hindi. English by default
$irctc->get_train_schedule(); // not necessary if want to check train details of given pnr or pass train number as a parameter
```

### Plugins


### Development

Help Us Making This library better
Currently Maintained by [Ankit Pise]

### Licensing
Released Under [MIT License]

### Note
This piece of code is issued as an experimental code. Use only for educational purposes. Author does not take any responsibility of any harmful / misuse caused by end user.

[PHP-Curl]:http://php.net/manual/en/book.curl.php
[Ankit Pise]:http://twitter.com/ankitpise
[MIT License]:https://github.com/ankitpise/OpenIrctc/blob/master/LICENSE
