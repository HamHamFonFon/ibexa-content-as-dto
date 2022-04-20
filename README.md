Ibexa content as DTO (data transform object)
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Requirements
----------------------------------------
 - PHP 7.4 or more
 - Ibexa 3.x

Installation
----------------------------------------

### Step 1: Download the Bundle

Add vcs repository to your composer.json :
``` 
"repositories": {
    ...
     "kaliop/ibexa-content-dto": {
         "type": "vcs",
         "url": "ssh://git@git.kaliop.net:7999/customers/kaliop/reuse/ibexa/ibexa-content-dto.git"
     }
},
```
Open a command console, enter your project directory and execute the 
following command to download the latest stable version of this bundle:

```console
$ composer require kaliop/ibexa-content-dto
```

For ezplatform 2.5 project, execute :
```console
$ composer require kaliop/ibexa-content-dto:dev-ezp
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project (for Ibexa 3.x):

```php
// config/bundles.php
return [
    // ...
    Kaliop\IbexaContentDto\IbexaContentDtoBundle::class => ['all' => true],
];
```

For ezPlatform 2.5
```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        ... more stuff here ...
        new Kaliop\IbexaContentDto\IbexaContentDtoBundle()
    ];
}

```


### Step 3 : Configure the Bundle
Configure `directory_repository` , `directory_dto` and `content_type_groups` values :
```yaml
# config/packages/ibx_content_dto.yaml
ibx_content_dto:
  directory_repository: src/Path/To/Repository/Directory
  directory_dto: src/Path/To/Dto/Directory
  content_type_groups: 
    - Content
    - Custom group...   
```

Basic usages
----------------------------------------

### Step 1: Create a couple DTO/Repository
### Step 2: Complete DTO
### Step 3: Get an ibexa content into DTO
### Step 4: Get a list of content into collection of DTO