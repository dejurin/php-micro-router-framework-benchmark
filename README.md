# PHP Micro & Router Framework Benchmark
I often use micro & router so I decided to do benchmark. This benchmark made thanks to ([@kenjis](https://github.com/kenjis)) [PHP-Framework-Benchmark](https://github.com/kenjis/php-framework-benchmark). I changed ab to awk, added to table "type of framework" and something else...

## PHP Framework Benchmark

**! Benchmark with help `wrk` https://github.com/wg/wrk**

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

If you find something wrong with my code, please feel free to send Pull Requests. But please note optimizing only for "Hello world!" is not acceptable. Building fastest "Hello world!" application is not the goal in this project.

## Results

### Benchmarking Environment

* CentOS Linux release 7.6.1810 
* PHP 7.3.1
* Apache/2.4.6

### Hello world! Benchmark

These are my benchmarks, not yours. **I encourage you to run on your (production equivalent) environments.**
(2019/01/27)

![Requests per second](img/WRK_screenshot-localhost-2019.01.27_1.png)
![Included files](img/WRK_screenshot-localhost-2019.01.27_2.png)
![Exec time](img/WRK_screenshot-localhost-2019.01.27_3.png)
![Memory (MB)](img/WRK_screenshot-localhost-2019.01.27_4.png)

|framework          |requests per second|relative|peak memory|relative|files|          type|
|-------------------|------------------:|-------:|----------:|-------:|----:|-------------:|
|no-framework       |          11,742.54|    19.7|       0.41|     1.0|    1|  no-framework|
|AltoRouter         |           3,619.92|     6.1|       0.50|     1.2|    6|        router|
|PHP-Router         |           3,358.57|     5.6|       0.52|     1.3|    8|        router|
|FastRoute          |           2,463.59|     4.1|       0.55|     1.3|   15|        router|
|flight             |           1,660.40|     2.8|       0.68|     1.6|   15|         micro|
|tipsy              |           1,617.18|     2.7|       0.66|     1.6|   16|         micro|
|limonade           |           1,439.16|     2.4|       1.02|     2.5|    6|         micro|
|siler-1.3          |           1,397.18|     2.3|       0.88|     2.1|   22|         micro|
|klein.php          |           1,271.65|     2.1|       0.78|     1.9|   20|        router|
|bearframework      |           1,065.35|     1.8|       0.94|     2.3|   22|         micro|
|fatfree            |           1,049.13|     1.8|       1.50|     3.6|    6|         micro|
|Slim-3.x           |             595.05|     1.0|       1.39|     3.4|   56|         micro|

Note(1): This benchmarks are limited by `wrk` https://github.com/wg/wrk performance.

## How to Benchmark

Install source code as <http://localhost/php-micro-router-framework-benchmark/>:

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
$ bash setup.sh PHP-Router/ flight/ lumen/
$ bash benchmark.sh PHP-Router/ flight/ lumen/
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
* [PHP Framework Benchmark](https://github.com/kenjis/php-framework-benchmark) ([@kenjis](https://github.com/kenjis))
* [wrk](https://github.com/wg/wrk) - Modern HTTP benchmarking tool
* [PHP-Router](https://github.com/dannyvankooten/PHP-Router) ([@dannyvankooten](https://github.com/dannyvankooten))
* [FatFree](http://fatfreeframework.com/) ([@phpfatfree](https://twitter.com/phpfatfree))
* [Flight](http://flightphp.com/)
* [Siler](https://github.com/leocavalcante/siler)
* [Tipsy](http://tipsy.la)
* [Limonade](https://limonade-php.github.io/)
* [AltoRouter](http://altorouter.com/)
* [FastRoute](https://github.com/nikic/FastRoute) ([@nikic](https://github.com/nikic))
* [Bear Framework](https://bearframework.com/)
* [Klein.php](https://github.com/klein/klein.php)
* [Slim](http://www.slimframework.com/) ([@slimphp](https://twitter.com/slimphp))
