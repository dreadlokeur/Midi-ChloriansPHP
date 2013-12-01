[![Flemwork](http://img4.hostingpics.net/pics/755466logodouble.png)](http://www.flemwork.com)

# Flemwork
### What is it ?

A modular PHP framework providing the strict minimum for any web application.

« Flem » closely means « Lazyness » in French => Flemwork wants to be lazy.
The laziness can be explained by the fact that Flemwork provides the strict minimum for a web application.

Each application has its own features and its own needs.
By default, Flemwork disables all its features. The developer must manually enable them according to the needs of the application.
So Flemwork not load everything that is unnecessary and avoids overloading the process.

That's what is called the « Flem » !

### What it provides ?
What each application needs ?
* ![Implemented](http://www.allmysms.com/_media/pictures/icons/iconeValid.png) Modular development
* ![Implemented](http://www.allmysms.com/_media/pictures/icons/iconeValid.png) Request/Response handler
* ![Not Implemented](http://www.novatis.tn/wp-content/themes/novatis/images/shortcode_icon/cross.png) User Input security
* ![Implemented](http://www.allmysms.com/_media/pictures/icons/iconeValid.png) Router
* ![Not Implemented](http://www.novatis.tn/wp-content/themes/novatis/images/shortcode_icon/cross.png) Cache system
* ![Implemented](http://www.allmysms.com/_media/pictures/icons/iconeValid.png) MVC architecture
* ![Not Implemented](http://www.novatis.tn/wp-content/themes/novatis/images/shortcode_icon/cross.png) Localization (Optional)
* Some basics utils

All this features is managed by Flemwork.
Flemwork doesn't use any library for the database management, template engine, orm engine...
The developer has to "teach" to Flemwork how to use them by the way of dedicated interfaces.

# Getting started
### Configuration
Just edit the `./Application/Settings.json` file.

### Develop your application
The development of the application is in the "Sources".

Each directory contains specific elements of the application:
* `./Application/Sources/Config` Contains the configuration files.
* `./Application/Sources/Controllers` Contains the controllers
* `./Application/Sources/Helpers` Contains light classes for specific tasks
* `./Application/Sources/Interfaces` Contains the interfaces of different engines.
* `./Application/Sources/Libraries` Contains the external libraries.
* `./Application/Sources/Locales` Contains the language translations.
* `./Application/Sources/Models` Contains the entities and the entity repositories.
* `./Application/Sources/Views` Contains the templates.
* `./Application/Sources/Web` Contains the accessible ressources.

If you opted for the modular development, your directory `./Application/Sources/` has to contain only one directory by module.
In each module directory, you have to follow the previous architecture.

An example:
```
$> ls Sources
Example AnotherModule MainModule
```

```
$> ls Sources/Example
Config Controllers Helpers Interfaces Libraries Locales Models Views Web
```

### Documentation
Writting...
