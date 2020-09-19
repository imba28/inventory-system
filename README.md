# Produkt Verleihsystem

## Installation

Benötigt:
* MySQL Server mit UTF-8 kodierter Datenbank.
* PHP >= 7.0

**oder**
* docker
* docker-compose

### mit Docker
```shell script
docker-compose up --build -d
docker-compose exec web bash
> composer install
> chown -R www-data: var && chown -R www-data: public/files && chown -R www-data: logs && chown -R www-data: vendor 
```

### direkt auf Hostsystem

Pakete installieren: `composer install`

Datebank Tabellen erstellen: siehe `create.sql`

Zum Entwickeln kann der integrierte Webserver von PHP verwendet werden (Routenformate funktionieren dann aber nicht):

`cd public && php -S localhost:8080`

## Konfiguration

`src/config/packages/app.yml`

```yaml
app:
    database:
        user: mmt_verleihsystem
        database: mmt_verleihsystem
        password: keins
```

`src/config/config.php`

```php
<?php
\App\Configuration::set('env', 'dev');
\App\Configuration::set('site_name', 'MMT Verleihmanager');
\App\Configuration::set('admin_email', 'max@mustermann.net');
?>
```

## Erweiterung

#### Routen

Neue Routen können mit Annotations direkt im Controller oder über die `src/config/routing.yml` definiert werden.
Mehr Infos dazu [hier](https://symfony.com/doc/current/routing.html#creating-routes).

#### legacy Router
> Achtung: das ist deprecated und sollte nicht mehr für neue Erweiterungen verwendet werden!

Neue Routen können in `src/config/routes.php` definiert werden. Benötigt einen Handler nach dem Schema  `CONTROLLER#METHODE`

z.B: `ProductController#main` => `ProductController::main()`

Routen können statische und dynamische Teile enthalten:

###### Beispiele:

`/products/search` => statische Teile

`/products/:id` => statischer Teil mit dynamischen Teil `id`

Dynamische Teile werden vom Container in die Action des aufgerufenen Controllers injected.

z.B:
Route `products/:id/:page`

Request URI:
`/products/5`

```php
class Controller {
    public function main($id, $page = 1)
    {
        // $params = ['id' => $id]
    }
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

```php
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