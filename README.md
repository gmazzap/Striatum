Striatum
========

![Striatum][1]

Striatum is a package (not full plugin) to handle WordPress hooks OOP way.

It makes use of [composer][2] to be embedded in larger projects and implements [Observer pattern][3] to deal with WordPress hooks. It takes the two pattern interfaces, [Subject][4] and [Observer][5], from PHP SPL library.

It is also a [Brain][6] module.

##Table of contents##

 - [Quick Start](#quick-start)
 - [Features](#features)
 - [More Info](#more-info)
  - [WordPress core compatible](#wordpress-core-compatible)
  - [The key "hook id" concept](#the-key-hook-id-concept)
  - [API](#api)
  - [Embed in OOP projects](#embed-in-oop-projects)
  - [Gotchas!](#gotchas)
  - [Requirements](#requirements)
  - [Installation](#installation)
  - [Codename: Striatum](#codename-striatum)
  - [Developers and Contributors](#developers-and-contributors)
  - [License](#license)

##Read also##

 - [API documentation](https://github.com/Giuseppe-Mazzapica/Striatum/blob/master/API.md)
  
##Quick Start##
Striatum is very easy to use, e.g. adding an action is as easy as:

    Brain\Hooks::addAction( 'myplugin.init', 'init', [ new MyPlugin, 'init' ] );
    
or fire a custom action or filter

    Brain\Hooks::trigger( 'foo_action', $optional_arg1, $optional_arg2, $and_so_on ); // action
    
    $filtered = Brain\Hooks::filter( 'bar_filter', $unfiltered, $optional_arg1 ); // filter

All the functions available through the `Brain\Hooks` API facade are documented in the API doc page. (Before teasing the *static* approach, read [here](#api)).

##Features##

 1. Add action and filters to core hooks, in absolutely core-compatible way
 2. Remove (or edit) any type of callback added, even objects methods or closures
 3. Auto-removing hooks: add callback that auto-remove themselves after a specific number of times they have been performed
 4. Hooks *freezing* and *unfreezing*: temporarily disable callbacks added to one or more hooks, and then enabling them again
 5. Add same callback to more than one hook using one function
 6. Advanced debug for hooks
 7. Fluent (chained) coding style

##More info##

In WordPress there are **hooks** and **callbacks**. An hook is identified by a tag (e.g. `'init'`, `'the_content'`) and for every tag is possible to have multiple callbacks attached. Every callback has two additional properties: priority and accepted arguments.
In Striatum, a tag has its own subject object, and rather than callbacks, Striatum uses observer objects, and every observer has properties that coincides with WordPress ones: callback, priority and accepted arguments. Additionally, Striatum observers have some additional properties, main one is **id**, but there are also 'times' and 'debug'. Another important property is 'is_filter', that when true makes a subject object be a filter rather than an action.

###WordPress core compatible###

Striatum is fully compatible with WordPress core hooks: in the concrete implementations of observer pattern interfaces, the subject methods `attach`, `detach` and `notify` are internally implemented using [WordPress Plugin API][7] functions.

###The key "hook id" concept###

To identify a specific callback, WordPress builds an unique id. When the callback is a string (plain functions) or an array of two strings (object static methods) that unique id is predictable, but when dynamic object methods are used, or worse, anonymous functions (closures), than WordPress uses [`spl_object_hash`][8] and to identify a specific callback becomes a pain.
For further info worth reading these two *WordPress Development* (Stack Exchange) answers, [this one][9] by @toscho (Thomas Scholz), and [this one][10] by myself.
Striatum force to **set an id property for every hook** (observer) added, in this way is possible to use that id to retrieve, remove, edit or debug the hook object.

###API###

Striatum package comes with an API that ease its usage, without having to get, instantiate or digging into package objects. API is defined in a class, stored in the Brain (Pimple) container with the id: `"hooks.api"`.
So is possible to get it using Brain instance, something like: `$api = Brain\Container::instance()->get("hooks.api")`, and then call all API function on the instance got in that way. However that's not very easy to use, especially for people used to just use a plain function to add and trigger hooks.
This is the reason why package also comes with a **facade class**. The term is not referred to [façade pattern][11], but more to [Laravel facades][12], whence the approach (not actual code) comes from: no *real* static method is present in the class, but a single `__callstatic` method that *proxy* API methods to proper instantiated objects.

The facade class is named `Hooks` inside Brain namespace. Using it, add an hook is as easy as:

    Brain\Hooks::addAction( 'plugin.init', 'init', [ new MyPlugin, 'init' ] );

`addAction` method is designed using almost same signature of core `add_action` function, so take almost same arguments. It differs for first argument that is the hook id: using `'plugin.init'`, is possible to retrieve, to edit, to remove and to debug the hook added.

All the functions available through the `Brain\Hooks` facade are documented in the [API documentation](https://github.com/Giuseppe-Mazzapica/Striatum/blob/master/API.md).

###Embed in OOP projects###

The static facade class is easy to use, however using in that way inside other classes, create there hardcoded dependency to Striatum. In addition, unit testing other classes in isolation becomes pratically impossible.
To solve these problems, the easiest way is to use composition via dependency injection.
In facts, the `Brain\Hooks` facade class can be used in dynamic way, like so:

    $hooks = new Brain\Hooks;
    $hooks->addAction( 'plugin.init', 'init', [ new MyPlugin, 'init' ] );
    
Looking at `Brain\Hooks` class code, you'll see there is absolutely no difference in the two methods, but using the latter is possible to inject an instance of the class inside other classes. See the following example:

    class A_Plugin_Class {
    
      function __construct( \Brain\Hooks $hooks ) {
        $this->hooks = $hooks;
      }
      
      function get_a_filtered_value( $a_value ) {
        return $this->hooks->filter( 'a_filter', $a_value, $this );
      }
      
    }

The method `get_a_filtered_value` makes use of `$this->hooks` property to call the Striatum API method.
Testing the method in isolation is very simple too, an example using PHPUnit and Mockery:

    class A_Plugin_Class_Test () {
    
      test_get_a_filtered_value() {
        $hooks = \Mockery::mock('\Brain\Hooks');
        $hooks->shouldReceive( 'filter' )->once()->with( 'a_filter', 'foo' )->andReturn( 'bar' );
        $class = new A_Plugin_Class( $hooks );
        $this->assertEquals( 'bar', $class->get_a_filtered_value( 'foo' ) );
      }
      
    }

So the method is tested in isolation, mocking the behavior of a filter: easy and straightforward.

If the classes had used the core `apply_filters` this simple test would be very hard and had required a testing package like the awesome [wp_mock by 10up][13] or the simpler, but less powerful [`HooksMock`][14] by myself.

###Gotchas!###

Striatum is a Brain module. As you can read in [Brain readme][15], it bootstrap itself and its modules on `after_setup_theme` with priority 0, this mean that you **can't use Striatum to attach callbacks to hooks that are triggered before `after_setup_theme`**.

There are not so many hooks fired before `after_setup_theme`, and all are available only for plugins and not themes, so if you use Striatum in themes you will not miss anything.
Main hooks **not** available in Striatum are: `muplugins_loaded` (only for mu-plugins), `plugins_loaded`, `sanitize_comment_cookies`, `setup_theme` and `load_textdomain`.

###Requirements###

 - PHP 5.4+
 - Composer (to install)
 - WordPress 3.9 (it *maybe* works with earlier versions, but it's not tested and versions < 3.9 will never supported).

###Installation###

You need [Composer][16] to install the package. It is hosted on [Packagist][17], so the only thing needed is insert `"brain/striatum": "dev-master"` in your `composer.json` `require` object

    {
        "require": {
            "php": ">=5.4",
            "brain/striatum": "dev-master"
        }
    }

See [Composer documentation][18] on how to install Composer itself, and packages. 
 
###Codename: Striatum###

The *Striatum*, also known as the *neostriatum* or *striate nucleus*, is a subcortical  part of the forebrain. Seems that human perception of timing resides in that part of brain.

Striatum is so called because is a [Brain][19] module, and it's function regards the *timing* aspect of WordPress: **hooks**.

###Developers & Contributors###

Package is open to contributors and pull requests. It comes with a set of unit tests written for [PHPUnit][20] suite. Please be sure all tests pass before submit a PR.
To run tests, please install package in stand-alone mode (i.e 'vendor' folder is inside package folder).
When installed in *dev* mode Striatum also install [Mockery][21], a powerful mocking test utility, and [HooksMock][22] a package that was written expressly to test Striatum.

###License###

Striatum own code is licensed under GPLv2+. Through Composer, it install code from:

 - [Composer][23] (MIT)
 - [Brain](https://github.com/Giuseppe-Mazzapica/Brain) (GPLv2+)
 - [Pimple][24] (MIT) - required by Brain -
 - [PHPUnit][25] (BSD-3-Clause) - only dev install -
 - [Mockery][26] (BSD-3-Clause) - only dev install -
 - [HooksMock][27] (GPLv2+) - only dev install -


  [1]: https://googledrive.com/host/0Bxo4bHbWEkMscmJNYkx6YXctaWM/striatum.png
  [2]: https://getcomposer.org/
  [3]: http://en.wikipedia.org/wiki/Observer_pattern
  [4]: http://www.php.net/manual/en/class.splsubject.php
  [5]: http://www.php.net/manual/en/class.splobserver.php
  [6]: https://github.com/Giuseppe-Mazzapica/Brain
  [7]: http://codex.wordpress.org/Plugin_API
  [8]: http://www.php.net/manual/en/function.spl-object-hash.php
  [9]: http://wordpress.stackexchange.com/a/57088/35541
  [10]: http://wordpress.stackexchange.com/a/140989/35541
  [11]: http://en.wikipedia.org/wiki/Facade_pattern
  [12]: http://laravel.com/docs/facades
  [13]: https://github.com/10up/wp_mock
  [14]: https://github.com/Giuseppe-Mazzapica/HooksMock
  [15]: https://github.com/Giuseppe-Mazzapica/Brain/blob/master/README.md
  [16]: https://getcomposer.org/
  [17]: https://packagist.org/
  [18]: https://getcomposer.org/doc/
  [19]: https://github.com/Giuseppe-Mazzapica/Brain
  [20]: http://phpunit.de/
  [21]: https://github.com/padraic/mockery
  [22]: https://github.com/Giuseppe-Mazzapica/HooksMock
  [23]: https://getcomposer.org/
  [24]: http://pimple.sensiolabs.org/
  [25]: http://phpunit.de/
  [26]: https://github.com/padraic/mockery
  [27]: https://github.com/Giuseppe-Mazzapica/HooksMock
