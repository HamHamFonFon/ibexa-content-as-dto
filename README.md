Ibexa content as DTO
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Introduction
----------------------------------------
This bundle give the possibility to visualize your contents data as a DTO (data transform object).
A DTO (Data Transform Object) is a simple object with properties and getters/setters who store data.
Ibexa Content object are complex and have a lot of information. A content object transformed into a DTO will be easier to read and use
because it will contain only fields value (in current language for multiple languages).


Requirements
----------------------------------------
 - PHP 8.1 or later
 - Ibexa 4.0 (Ibexa DXP) / Symfony 5.*

Installation
----------------------------------------

### Step 1: Install Bundle


```console
$ composer require HamHamFonFon/ibexa-content-dto
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php
return [
    // ...
    HamHamFonFon\IbexaContentDto\IbexaContentDtoBundle::class => ['all' => true],
];
```


### Step 3 : Configure the Bundle
Configure `directory_repository` , `directory_dto` and `content_type_groups` values :

- directory_repository: path where repositories will be created (e.g.: src/Repository/)
- directory_dto: path where DTO will be created (e.g.: src/Entity/DTO/)
- content_type_groups: list of content-groups ("Content" by default)

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
DTO works in couple with a repository linked to it. DTO contains only fields values and repository retrieve data from your back-office helped with Ibexa repositories service.

### Step 1: Create a couple DTO/Repository

There is two ways to create a couple DTO/repository

#### Manually:
Create your DTO class, add properties (camelcase-style). Properties name must be same as fields identifier but in camel-case style.
Your DTO needs to extend `AbstractDto` class and implements `DtoInterface` interface. Add getters/setters and mandatory methods.

Example, you have a content-type `article` with fields `title` (ezstring), `title_long` (ezstring), `image_header` (ezimage), `text` (ezrichtext), `related_articles` (ezobjectrelationlist) 
Your DTO will be like this :
```php 

use HamHamFonFon\IbexaContentDto\Entity\DtoInterface;
use HamHamFonFon\IbexaContentDto\Entity\Dto\AbstractDto;

class Article extends AbstractDto implements DtoInterface
{
    protected ?string $title;
    protected ?string $titleLong;
    protected $image;
    protected ?DOMDocument $text;
    protected ListDto $relatedArticles;

    // List of getters and setters...

    /**
     * @return array|null
     */
    public function listObjectRelationListFields(): ?array
    {
        return ['relatedArticles'];
    }
}
```
You can add your own methods in the DTO if you have specific needs.

#### Automatically
Just use symfony command `php bin/console dto:create`, both classes will be generated.

### Step 2: Complete DTO
### Step 3: Get an ibexa content into DTO
### Step 4: Get a list of content into collection of DTO

Future evolution
----------------------------------------
 - Create or update content from a DTO

Contributes
----------------------------------------
I accept contributions, please fork the project and submit pull requests.

Bugs and issues
----------------------------------------
In case you find some bugs or have question about this repository, open an issue and I will answer you as soon as possible.

Authors
----------------------------------------
Stéphane MEAUDRE <balistik.fonfon@gmail.com>
