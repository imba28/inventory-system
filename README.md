# Produkt Verleihsystem

## Installation

Benötigt:
* MySQL Server mit UTF-8 kodierter Datenbank.
* PHP >= 7.0

Pakete installieren: `composer install`

Datebank Tabellen erstellen:

```sql
CREATE TABLE `PREFIX_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rentDate` datetime DEFAULT NULL,
  `returnDate` datetime DEFAULT NULL,
  `expectedReturnDate` datetime DEFAULT NULL,
  `createDate` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `CUSTOMER` (`customer_id`),
  KEY `PRODUCT` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `internal_id` varchar(100) DEFAULT NULL,
  `createDate` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `internal_id_UNIQUE` (`internal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_inventurproducts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `inventur_id` int(11) NOT NULL,
  `in_stock` enum('0','1') NOT NULL,
  `missing` enum('0','1') NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  `createDate` datetime NOT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_inventurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `startDate` datetime DEFAULT NULL,
  `finishDate` datetime DEFAULT NULL,
  `deleted` enum('1','0') NOT NULL DEFAULT '0',
  `createDate` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('error','info','warning','debug') NOT NULL DEFAULT 'info',
  `message` text,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `createDate` datetime NOT NULL,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_productimages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `src` varchar(500) DEFAULT NULL,
  `deleted` enum('1','0') NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `createDate` datetime NOT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(500) NOT NULL,
  `invNr` varchar(45) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `note` text,
  `description` text,
  `condition` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `createDate` datetime NOT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `invNr` (`invNr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `password` varchar(64) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `createDate` datetime NOT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```
Zum Entwickeln kann der integrierte Webserver von PHP verwendet werden:

`php -S localhost:8080 index.php`

## Konfiguration

`src/config/config.php`

```php
<?php
\App\Configuration::set('env', 'dev');
\App\Configuration::set('site_name', 'MMT Verleihmanager');
\App\Configuration::set('admin_email', 'max@mustermann.net');

\App\Configuration::set('DB_HOST', '127.0.0.1');
\App\Configuration::set('DB_DB', 'verleih');
\App\Configuration::set('DB_PORT', '3306');
\App\Configuration::set('DB_USER', 'db_user');
\App\Configuration::set('DB_PWD', '');
\App\Configuration::set('DB_PREFIX', 'v');
?>
```

## Erweiterung

#### Routen

Neue Routen können in `src/config/routes.php` definiert werden. Benötigt einen Handler nach dem Schema  `CONTROLLER#METHODE`

z.B: `ProductController#main` => `ProductController::main()`

Routen können statische und dynamische Teile enthalten:

###### Beispiele:

`/products/search` => statische Teile

`/products/:id` => statischer Teil mit dynamischen Teil `id`

Dynamische Teile werden als Argument `$params` an die Methode des Controllers übergeben.

z.B:
Route `products/:id`

Request URI:
`/products/5`

```
class Controller {
    public function main($params)
        // $params = array('id' => 5)
    ));
}
```

#### Models

Models besitzen Attribute, die automatisch aus der / in die Datenbank gemappt werden. Einfache Columns werden übernommen, Fremdschlüssel nach dem Schema `MODEL_id` werden automatisch in eine Beziehung gesetzt.

Beispiel:
Tabelle `Products`
```
|id|name|material|product_id|user_id|
-------------------------------------
|42|Bank|  Holz  |     1    |   2   |
```

wird automatisch zu (falls es ein Model für Product und User gibt)
```json
{
    "id": 42,
    "name": "Bank",
    "material": "Holz",
    "product": {
        "id": 1,
        ...
    },
    "user": {
        "id": 2,
        ...
    }
}
```

Falls der Fremdschlüssel in einer anderen Tabelle gespeichert ist, lässt sich mit folgendem Code eine Collection erzeugen:

```
public function images(): \Traversable
{
    return $this->hasMany('ProductsImage');
}
```

Neue Models benötigen:

eine Tabelle mit Namen des Modells im Plural und mindestens folgenden Feldern:

1. `id` int(11) PRIMARY_KEY NOT NULL
2. `user_id` int(11) DEFAULT NULL
3. `createDate` datetime NOT NULL
4. `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
5. `deleted` enum('0','1') NOT NULL DEFAULT '0'

eine Model Klasse nach folgendem Schema gespeichert in `src/classes/models`:
Beispiel:
```php
<?php
namespace App\Models;

use \App\Model;
use \Traversable;

class Product extends Model
{
    protected $attributes = ['name', 'material'];

    protected function init()
    {
        foreach ($this->images() as $image) {
            echo $image->get('title');
        }
    }

    // 1:n Beziehung
    public function images(): Traversable
    {
        return $this->hasMany('ProductsImage');
    }
}
?>
```

#### Views

Views befinden sich in `src/views` und müssen immer die Dateiendung `html.twig` besitzen.
Views werden mit Hilfe der PHP Template Engine  [Twig](https://twig.symfony.com/) gerendert.