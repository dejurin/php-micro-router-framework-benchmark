# PHP Micro & Router Framework Benchmark
I often use micro & router so I decided to do benchmark. This benchmark made thanks to ([@kenjis](https://github.com/kenjis)) [PHP-Framework-Benchmark](https://github.com/kenjis/php-framework-benchmark). This is not my development, I just copied and made my compilation.

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
  
