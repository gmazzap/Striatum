Striatum API Docs
================

All the API methods can be called using the facade class `Brain\Hooks`, like so:

    Brain\Hooks::a_method_name()
    
Alternatively, is possible to use method using an instance of same class:

    $hooks = new Brain\Hooks;
    
    $hooks->a_method_name()
    

Index
-----

 - [addAction](#addAction)
 - [addFilter](#addFilter)
 - [addHook](#addHook)
 - [trigger](#trigger)
 - [filter](#filter)
 - [updateHook](#updateHook)
 - [getHooks](#getHooks)
 - [getHook](#getHook)
 - [removeHook](#removeHook)
 - [removeHooks](#removeHooks)
 - [freezeHooks](#freezeHooks)
 - [unfreezeHooks](#unfreezeHooks)
 - [hookHas](#hookHas)
 - [actionHas](#actionHas)
 - [filterHas](#filterHas)
 - [doingCallback](#doingCallback)
 - [callbackDone](#callbackDone)
 - [callbackLastArgs](#callbackLastArgs)
 - [doingPriority](#doingPriority)

addAction
-------------

Add an observer to an action hook. This is the homologous of core `add_action` and takes almost same arguments.
There are 2 different arguments, the first and the last.
The first is the observer id, that can be used to retrieve/update/remove the observer.
The last is 'times' that makes the callback run a given number of times, so creating
sort of self-removing observer.
Unlike core add_action is possible to add the same observer to different hooks, passing an
array or a pipe separed list of action.

###Signature###
     
    public function addAction( $id = '', $hook = '', $callback = NULL, $priority = 10, $args_num = 1, $times = 0 )

###Params###

 - string **`$id`** Observer id
 - string | array **`$hook`** Action or filter tag, e.g. 'init' or 'the_title'. Is possible to pass an array or a pipe separed list of tags.
 - callable **`$callback`** The callback to associate to observer
 - int **`$priority`** Observer priority. Default 10.
 - int **`$args_num`** Accepted arguments number. Default 1.
 - int **`$times`** Make observer run a given number of times. Default 0 (no limit).

###Return###

If more than one hook is added it return an array oh hook objects. If only one hook is added, it's returned. If something goes wrong it return a WP_Error instance.
    
###Additional Info###
    
Once the method returns an hook objects is possible to call on it some methods to set properties, e.g.

    $action = Brain\Hooks::addAction( 'foo_action', 'init', 'a_callback')->setPriority(10)->setTimes(4);
    
Regarding `setTimes` methods, there are some alias that can be used, e.g. `runOnce()`,  `runTwice()` or `run{$n}times()` where `{$n}` can be replaced with any number, e.g. `run3times()`, `run7times()` and so on.


----------
    

addFilter
---------

Add an observer to a filter hook. This is the homologous of core `add_filter` and takes almost same arguments.
There are 2 different arguments, the first and the last.
The first is the observer id, that can be used to retrieve / update / remove the observer.
The last is 'times' that makes the callback run a given number of times, so creating sort of self-removing observer.
Unlike core add_filter is possible to add the same observer to different hooks, passing an array or a pipe separed list of filters.

###Signature###
     
    public function addFilter( $id = '', $hook = '', $callback = NULL, $priority = 10, $args_num = 1, $times = 0 )

###Params###

 - string **`$id`** Observer id
 - string | array **`$hook`** Action or filter tag, e.g. 'init' or 'the_title'. Is possible to use an array or a pipe separed list of tags.
 - callable **`$callback`** The callback to associate to observer
 - int **`$priority`** Observer priority. Default 10.
 - int **`$args_num`** Accepted arguments number. Default 1.
 - int **`$times`** Make observer run a given number of times. Default 0 (no limit)

###Return###

If more than one hook is added it return an array oh hook objects. If only one hook is added, it's returned. If something goes wrong it return a WP_Error instance.
    
###Additional Info###
    
Once the method returns an hook objects is possible to call on it some methods to set properties, e.g.

    $filter = Brain\Hooks::addFilter( 'foo_filter', 'the_title', 'a_callback')->setPriority(10)->setTimes(4);
    
Regarding `setTimes` methods, there are some alias that can be used, e.g. `runOnce()`,  `runTwice()` or `run{$n}times()` where `{$n}` can be replaced with any number, e.g. `run3times()`, `run7times()` and so on.


----------


addHook
-------

Add an observer to an hook. This is the homologous of core `add_action` and `add_filter`.
The two types of hook are differentiated via `$is_filter` param.
This method is designed differently from core functions, but the two methods that make use it,
`addFilter` and `addAction` accepts same arguments of core ones.
Only additional arguments is 'times', that makes the callback runs a given number of times.
Using this method is possible to add same callback to more than one hook.
Return the array of observer objects added or the single instance when only one is added.

###Signature###

    public function addHook( $id = '', $args = [ ], $hook = '', $is_filter = FALSE )
    
###Params###

 - string **`$id`** Observer id
 - array **`$args`** Observer args: callback, priority, accepted args and allowed times.
 - string | array **`$hook`** Action or filter tag, e.g. 'init' or 'the_title'. Is possible to pass an array or a pipe separed list of tags.
 - boolean **`$is_filter`** If true add a filter, otherwise an action

###Return###

If more than one hook is added it return an array oh hook objects. If only one hook is added, it's returned. If something goes wrong it return a WP_Error instance.


----------


trigger
-------

Trigger an hook. Homologous for core `do_action` and `apply_filters`.
If the hook is a filter than the method return whatever returned by all the attached observer callbacks.

###Signature###

    public function trigger( $hook = '' )
    
###Params###

    string $hook Action or filter tag, e.g. 'init' or 'the_title'

###Return###

When the hook is a filter method returns the filters results. Nothing is returned for actions.

###Additional Info###

Just like `do_action` and `apply_filters` this method accepts any optional arguments that is passed to hooked callbacks, so is possible to use like so: 

    \Brain\Hooks::trigger( 'my_custom_hook', $var1, $var2, $var3 );
    

----------


filter
------

Homologous for core `apply_filters`, it can be used to change a value of some variable using hooks. Hooked callback must return a value.

###Signature###

    public function filter( $hook = '', $actual = NULL )
    
###Params###

 - string **`$hook`** Action or filter tag, e.g. 'init' or 'the_title'
 - mixed **`$actual`** Actual value to be filtered

###Return###

Whaterver is returned by hooked callbacks, or actual value (2nd argument) if there are no hooked functions.

###Additional Info###

Just like `apply_filters` this method accepts any optional arguments that is passed to hooked callbacks, so is possible to use like so: 

    \Brain\Hooks::filter( 'my_custom_hook', 'The value as is now.', $var1, $var2, $var3 );


----------

updateHook
----------

 Modify an observer after it is added. This task is not not available in core plugin API.
Every aspect of the observer can be changed.
If the given observer id does not exists the method act as `addHook`, adding a new hook.

###Signature###

    public function updateHook( $hook = '', $observer = '', $args = [ ], $is_filter = FALSE )
    
###Params###

 - string **`$hook`** Action or filter tag, e.g. 'init'
 - string | \Brain\Striatum\HookInterface **`$observer`** Observer id or object
 - array **`$args`** New observer params
 - bool **`$is_filter`** True if the hook is filter. It matters only if the observer is not found and method attemp to create a new one.

###Return###

Return the hook object that has been updated or a WP_Error instance if something goes wrong.


----------


getHooks
--------

Given an action or filter hook (e.g. 'init' or 'the_title') returns the hook object attached.
Is possible to return the subject instance used for the hook instead of hooks array.

###Signature###

    public function getHooks( $hook = '', $get_subject = FALSE )
    
###Params###

 - string **`$hook`** Action or filter tag to get the attached hooks for
 - boolean **`$get_subject`** If true return the hook subject instead of hooks objects array

###Return###

By default return an array of hook objects hooked to a specific hook tag. Setting `$get_subject` to true, method return the subject object instance related to the hook tag. In both cases, `NULL` is returned if there are no hooks objects attached to the hook tag. A `WP_Error` instance is returned if something goes wrong.

###Additional Info###

When the method returns an array of hook objects, that array is keyed with the hook ids, so e.g.

    $init_hooks = Brain\Hooks:: getHooks( 'init' );
    
If there is an hook with the id `'my_plugin_init'` is possible to get it using:

    $my_plugin_init = $init_hooks['my_plugin_init'];

From the retrieved hook object is possible to get some properties, like priority, callback, number of accepted args or times, in different ways: `get` method, specific getter, directly access, or `ArrayAccess` interfaces:

    $times = $my_plugin_init->get( 'times' );
    
    $priority = $my_plugin_init->getPriority();
    
    $args_num = $my_plugin_init->args_num
    
    $callback = $my_plugin_init['callback'];
    
These methods, are **not** available to set properties, at least not for all properties. The properties that can be set using these methods are 'callback' and 'times'.

Regarding 'times', there are also some alias that can be used, e.g. `runOnce()`,  `runTwice()` or `run{$n}times()` where `{$n}` can be replaced with any number, e.g. `run3times()`, `run7times()` and so on.

Following 5 lines do the same thing:

     $my_plugin_init->set( 'times', 1 );
     
     $my_plugin_init->setTimes( 1 );
     
     $my_plugin_init->times = 1;
     
     $my_plugin_init['times'] = 1;
     
     $my_plugin_init->runOnce();
     
Same methods (but the last one) can be used to change 'callback' property.

To be able to edit all the properties, use the [`updateHook`](#updateHook) method.


----------


getHook
-------

Given an action or filter hook (e.g. 'init' or 'the_title') and an observer id return the observer object.

###Signature###

    public function getHook( $hook = '', $id = '' )

###Params###

 - string | \Brain\Striatum\SubjectInterface **`$hook`** Action or filter tag to get
 - string **`$id`** Observer id

###Return###
 
The required object instance id exists, or `NULL` otherwise.  A `WP_Error` instance is returned if something goes wrong.

###Additional Info###

From the retrieved hook object is possible to get some properties, like priority, callback, number of accepted args or times, in different ways: `get` method, specific getter, directly access, or `ArrayAccess` interfaces:

    $times = $retrieved_hook->get( 'times' );
    
    $priority = $retrieved_hook->getPriority();
    
    $args_num = $retrieved_hook->args_num
    
    $callback = $retrieved_hook['callback'];
    
These methods, are **not** available to set properties, at least not for all properties. The properties that can be set using these methods are 'callback' and 'times'.

Regarding 'times', there are also some alias that can be used, e.g. `runOnce()`,  `runTwice()` or `run{$n}times()` where `{$n}` can be replaced with any number, e.g. `run3times()`, `run7times()` and so on.

Following 5 lines do the same thing:

     $retrieved_hook->set( 'times', 1 );
     
     $retrieved_hook->setTimes( 1 );
     
     $retrieved_hook->times = 1;
     
     $retrieved_hook['times'] = 1;
     
     $retrieved_hook->runOnce();
     
Same methods (but the last one) can be used to change 'callback' property.

To be able to edit all the properties, use the [`updateHook`](#updateHook) method. 

----------



removeHook
----------

Remove an observer form an hook. Is the homologous for core `remove_action` and `remove_filter`.
However instead of passing the callback to remove (that is hard when is an object method and harder when is a closure) the methods accepts the observer id, so is very easy remove any type of callback.

###Signature###

    public function removeHook( $hook = '', $id = '' )
    
###Params###

 - string **`$hook`** Action or filter tag, e.g. 'init' or 'the_title'
 - string **`$id`** Observer id. Is first param passed to `addAction`, `addFilter` or `addHook`

###Return###

Normally, nothing, but returns a `WP_Error` instance if something goes wrong. 


----------

   

removeHooks
-----------

Remove all observers form an hook. Is similar to core `remove_all_actions()`.
However it accepts also an array or a pipe separed lists of actions and/or filters to remove all observers from different hooks.

###Signature###

     public function removeHooks( $hooks = '' )

###Params###

string | array **`$hooks`** Action or filter tag, e.g. 'init' or 'the_title'. Is possible to pass an array or a pipe separed list of tags.

###Return###

Normally, nothing, but returns a `WP_Error` instance if something goes wrong. 


----------



freezeHooks
-----------

Freezing an hook means that all the observer and their params are saved, however they are temporarly suspended, so do nothing until they are not unfreezed.
Is possible to freeze more than one hook with a single call, using an array or a pipe separed list of hook.

###Signature###

    public function freezeHooks( $hooks = '' )

###Params###

string | array **`$hooks`** Action or filter tag, e.g. 'init' or 'the_title'. Is possible to pass an array or a pipe separed list of tags.

###Return###

Normally, nothing, but returns a `WP_Error` instance if something goes wrong. 


----------


   

unfreezeHooks
-------------

Unfreeze one or more hooks previously frozen using `freezeHooks()`

###Signature###

    public function unfreezeHooks( $hooks = '' )
    
###Params###

string | array **`$hooks`** Action or filter tag, e.g. 'init' or 'the_title'. Is possible to pass an array or a pipe separed list of tags.

###Return###

Normally, nothing, but returns a `WP_Error` instance if something goes wrong. 


----------

    

hookHas
-------

Given an hook (or a subject object) and an observer id, check if the observer is added to the hook. If true, returns true unless the `$return_hook` argument is true, in that case returns the hook object.

###Signature###

    public function hookHas( $hook = '', $id = '', $return_hook = FALSE )

###Params###

 - string | \Brain\Striatum\SubjectInterface **`$hook`** Action or filter tag, e.g. 'init' or 'the_title'
 - string **`$id`** Observer id
 - boolean **`$return_hook`** If true, returns hook object instead of `TRUE`. No effect when check is false.

###Return###

By default return a boolean: true if the hook object with given id is attached to the given hook tag, false otherwise. When 3rd argument is true, and the hook exists, then the hook object is returned. A `WP_Error` instance if returned if something goes wrong.


----------

    

actionHas
---------

Given an hook (or a subject object) and an observer id returns true if the observer is added to the hook and the hook is an action. False otherwise.

###Signature###

    public function actionHas( $hook = '', $id = '' )

###Params###

 - string | \Brain\Striatum\SubjectInterface **`$hook`** Action tag, e.g. 'init'
 - string **`$id`** Observer id

###Return###
 
Return a boolean: true if the hook object with given id is attached to the given hook tag, false otherwise. A `WP_Error` instance if returned if something goes wrong.


----------


filterHas
---------

Given an hook (or a subject object) and an observer id returns true if the observer is added to the hook and the hook is a filter. False otherwise.

###Signature###

    public function filterHas( $hook = '', $id = '' )

###Params###

 - string | \Brain\Striatum\SubjectInterface **`$hook`** Filter tag, e.g. 'the_title'
 - string **`$id`** Observer id

###Return###
 
Return a boolean: true if the hook object with given id is attached to the given hook tag, false otherwise. A `WP_Error` instance if returned if something goes wrong.


----------

    

doingCallback
---------------

Similar to core `doing_action()` (introduced with WP 3.9) given an hook and an observer id returns true if the observer is being performed, even inside nested callbacks or nested hooks.

###Signature###

    public function doingCallback( $hook = '', $id = '' )
    
###Params###

 - string **`$hook`** Action or filter tag, e.g. 'init' or 'the_title' string
 - string **`$id`** Observer id

###Return###
 
Return a boolean: true if the hook object with given id is being performed from the given hook tag, false otherwise.


----------


callbackDone
------------

Similar to core `did_action()` given an hook and an observer id returns true if the observer callback was performed. Unlike `did_action()` this method works also for filters and returns false when the callback is being performed.

###Signature###

    public function callbackDone( $hook = '', $id = '' )

###Params###

 - string **`$hook`** Action or filter tag, e.g. 'init' or 'the_title' string
 - string **`$id`** Observer id

###Return###
 
Return a boolean: true if the hook object with given id was performed by the given hook tag, false otherwise.


----------


    

callbackLast
------------

Given an hook, return the last callback id (if any) ran for the hook.

###Signature###

    public function callbackLast( $hook = '' )

###Params###

 - string **`$hook`** Action or filter tag, e.g. 'init' or 'the_title' string

###Return###
 
Return the id of the last hook object performed by an hook tag. Return `NULL` if the hook tag was not performed or there was no hook object attached.


----------


    

callbackLastArgs
----------------

Given an hook and an observer id, return the last arguments array passed to the callback.

###Signature###

    public function callbackLastArgs( $hook = '', $id = '' )
    
###Params###

 - string **`$hook`** Action or filter tag, e.g. 'init' or 'the_title' string
 - string **`$id`** Observer id

###Return###
 
If the given observer object was performed by the given hook, return the last arguments passed to the callback. 


----------



doingPriority
-------------

Given an hook, if is currently running, return the priority being performed.
Returns false if no observer are added to hook or it is not currently performed.

###Signature###

    public function doingPriority( $hook = '' )

###Params###

 - string **`$hook`** Action or filter tag, e.g. 'init' or 'the_title' string

###Return###
 
If the given observer is being performed return the current priority. Return false if the hook is not being performed.
