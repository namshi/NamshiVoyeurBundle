## NAMSHI | VoyeurBundle

This bundle is made to ease testing
and comparing frontend changes in your
applications.

With it, you can first take screenshots
of your webpages and then compare them.

A common scenario is to check differences
between one particular version of your application
and another one (for example, `master` and `develop`).

## Voyeur

In order to start taking screenshots,
you will just need to configure
**services** and **parameters**:

``` yml
parameters:
    namshi_voyeur:
      browsers:
        - firefox
        - safari
        - chrome
      urls:
        homepage:     "/"
        new-arrivals: "mail"
      shots_dir: "/Users/xx/Downloads/screenshots"
      base_url:       "http://google.com/"
```

You will have to tell the bundle
with which browsers you want to take
screenshots, at which URLs, a base URL and
a directory where the screenshots will
be saved.

Then, configure the services:

```yml
services:
    safari:
        class:  Behat\Mink\Driver\Selenium2Driver
        calls:
          - [start]
        arguments:
          browser: safari
    firefox:
        class:  Behat\Mink\Driver\Selenium2Driver
        calls:
          - [start]
    chrome:
        class:  Behat\Mink\Driver\Selenium2Driver
        calls:
          - [start]
        arguments:
          browser: chrome
```

which are basically instances of the Selenium2
driver, which will be used by Voyeur.

Last step is to launch Voyeur from the command line:

```bash
php app/console namshi:voyeur
```

## Voyeur:diff

To generate the diffs between different screenshots
captured by Voyeur, just trigger the command:

```bash
php app/console namshi:voyeur:diff path/to/first/screenshots path/to/other/screenshots
```

You can also specify the path to save the diffs at:

```bash
--diff-dir=path/to/diffs
```

otherwise they will be saved at `path/to/other/screenshots`.

## Testing different websites

You can optionally specify different configurations:

``` yml
parameters:
    namshi_voyeur:
      browsers:
        - firefox
      urls:
        homepage:     "/"
        new-arrivals: "mail"
      shots_dir: "/Users/xx/Downloads/screenshots"
      base_url:       "http://google.com/"
    voyeur_ae:
      base_url:       "http://google.ae/"
    voyeur_de:
      base_url:       "http://google.de/"
    voyeur_it:
      base_url:       "http://google.it/"
```
and then run Voyeur with the specified configuration:

``` bash
php app/console namshi:voyeur --config=voyeur_de
```

The example above will run Voyeur on `google.de`, instead of `.com`.
