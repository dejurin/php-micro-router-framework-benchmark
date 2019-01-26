# PHP Micro & Router Framework Benchmark
I often use micro & router so I decided to do benchmark. This benchmark made thanks to ([@kenjis](https://github.com/kenjis)) [PHP-Framework-Benchmark](https://github.com/kenjis/php-framework-benchmark). This is not my development, I just fork and made my compilation.

## PHP Framework Benchmark

This project attempts to measure minimum overhead (minimum bootstrap cost) of PHP frameworks in the real world.

So I think the minimum applications to benchmark should not include:

* cost of template engine (HTML output)
* cost of database manipulation
* cost of debugging information

Components like Template engine or ORM/Database libraries are out of scope in this project.

## Benchmarking Policy

This is `master` branch.

* Install a framework according to the official documentation.
* Use the default configuration.
  * Don't remove any components/configurations even if they are not used.
  * With minimum changes to run this benchmark.
* Set environment production/Turn off debug mode.
* Run optimization which you normally do in your production environment, like Composer's `--optimize-autoloader`.
* Use controller or action class if a framework has the functionality.

Some people may think using default configuration is not fair. But I think a framework's default configuration is an assertion of what it is. Default configuration is a good starting point to know a framework. And I can't optimize all the frameworks. Some frameworks are optimized, some are not, it is not fair. So I don't remove any components/configurations.

But if you are interested in benchmarking with optimization (removing components/configurations which are not used), See [optimize](https://github.com/kenjis/php-framework-benchmark/tree/optimize) branch.

If you find something wrong with my code, please feel free to send Pull Requests. But please note optimizing only for "Hello World" is not acceptable. Building fastest "Hello World" application is not the goal in this project.

## Results

### Benchmarking Environment
* macOS Mojave 10.14.2
* Processor Name: Intel Core i7
* Processor Speed: 4.2 GHz
* Memory: 40 GB
* PHP 7.3.1 (cli) (built: Jan 10 2019 13:15:37) ( NTS )
* Zend Engine v3.3.1, Copyright (c) 1998-2018 Zend Technologies with Zend OPcache v7.3.1, Copyright (c) 1999-2018, by Zend Technologies
* Apache/2.4.34 (Unix)

### Hello World Benchmark

These are my benchmarks, not yours. **I encourage you to run on your (production equivalent) environments.**
(2019/01/27)

![Benchmark Results Graph](img/screenshot-localhost-2019.01.27.png)

|framework          |requests per second|relative|peak memory|relative|type|
|-------------------|------------------:|-------:|----------:|-------:|-------:|
|no-framework       |           1,994.65|10,498.2|       0.37|     1.0|no-framework|
|PHP-Router         |           1,458.65| 7,677.1|       0.47|     1.3|router|
|fatfree            |           1,115.54| 5,871.3|       1.39|     3.7|micro|
|flight             |             746.61| 3,929.5|       0.63|     1.7|micro|
|siler-1.3          |             198.54| 1,044.9|       0.76|     2.0|micro|
|tipsy              |             123.81|   651.6|       0.61|     1.6|micro|
|limonade           |             118.68|   624.6|       0.94|     2.5|micro|
|AltoRouter         |              68.69|   361.5|       0.41|     1.1|router|
|lumen              |              60.61|   319.0|       2.88|     7.8|micro|
|FastRoute          |              38.27|   201.4|       0.51|     1.4|router|
|bearframework      |              37.63|   198.1|       1.09|     2.9|micro|
|klein.php          |               0.20|     1.1|       0.74|     2.0|router|
|Slim-3.x           |               0.19|     1.0|       1.30|     3.5|micro|

Note(1): Please note that the two frameworks: klein.php, Slim-3.x are significantly smaller than the others. I honestly do not understand why this is so, and I will be grateful if someone explains it.

Note(2): This benchmarks are limited by `ab` performance. See [#62](https://github.com/kenjis/php-framework-benchmark/issues/62).

## How to Benchmark

If you want to benchmark PHP extension frameworks like Phalcon, you need to install the extenstions.

Install source code as <http://localhost/php-framework-benchmark/>:

~~~
$ git clone https://github.com/dejurin/php-micro-router-framework-benchmark.git
$ cd php-micro-router-framework-benchmark
$ bash setup.sh
~~~

Run benchmarks:

~~~
$ bash benchmark.sh
~~~

See <http://localhost/php-micro-router-framework-benchmark/>.

If you want to benchmark some frameworks:

~~~
$ bash setup.sh PHP-Router/ flight/ lumen-5.1/ lumen/
$ bash benchmark.sh PHP-Router/ flight/ lumen-5.1/ lumen/
~~~

## Linux Kernel Configuration

I added below in `/etc/sysctl.conf`

~~~
# Added
net.netfilter.nf_conntrack_max = 100000
net.nf_conntrack_max = 100000
net.ipv4.tcp_max_tw_buckets = 180000
net.ipv4.tcp_tw_recycle = 1
net.ipv4.tcp_tw_reuse = 1
net.ipv4.tcp_fin_timeout = 10
~~~

and run `sudo sysctl -p`.

If you want to see current configuration, run `sudo sysctl -a`.

## References

* [PHP-Router](https://github.com/dannyvankooten/PHP-Router) ([@dannyvankooten](https://github.com/dannyvankooten))
* [FatFree](http://fatfreeframework.com/) ([@phpfatfree](https://twitter.com/phpfatfree))
* [Flight](http://flightphp.com/)
* [Siler](https://github.com/leocavalcante/siler)
* [Tipsy](http://tipsy.la)
* [Limonade](https://limonade-php.github.io/)
* [AltoRouter](http://altorouter.com/)
* [Lumen](http://lumen.laravel.com/)
* [FastRoute](https://github.com/nikic/FastRoute) ([@nikic](https://github.com/nikic))
* [Bear Framework](https://bearframework.com/)
* [Klein.php](https://github.com/klein/klein.php)
* [Slim](http://www.slimframework.com/) ([@slimphp](https://twitter.com/slimphp))
