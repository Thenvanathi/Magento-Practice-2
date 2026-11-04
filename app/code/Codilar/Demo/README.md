# Codilar_Demo

A small learning module that shows how a Magento 2 storefront page is built using the MVC pattern. It also declares a database table for future form submissions.

## What this module does

- Adds a storefront page at `/demo/index/index`.
- The page shows a heading ("Welcome to Codilar Demo Module!") and a message that comes from a Model class.
- Declares the table `codilar_demo_submission` (name, email, message, created date). The table is created, but nothing uses it yet.

## How the page is built

```
Browser request: /demo/index/index
        |
        v
routes.xml            -> frontName "demo" is mapped to this module
        |
        v
Controller/Index/Index.php   -> handles the GET request, returns a page
        |
        v
view/frontend/layout/demo_index_index.xml  -> puts our block in the "content" area
        |
        v
Block/Message.php     -> gets the text from the Model
        |
        v
Model/Message.php     -> returns "Hello from the Magento Model!"
        |
        v
view/frontend/templates/message.phtml      -> prints the heading and the message
```

## File guide

| File | Purpose |
|---|---|
| `registration.php` | Tells Magento the module exists. |
| `etc/module.xml` | Declares the module name; loads after `Magento_Store`. |
| `etc/frontend/routes.xml` | Maps the URL prefix `demo` to this module. |
| `Controller/Index/Index.php` | Handles `/demo/index/index` (GET only). |
| `view/frontend/layout/demo_index_index.xml` | Layout file for that page. Its name is `<route>_<controller>_<action>`. |
| `Block/Message.php` | Connects the Model to the template. |
| `Model/Message.php` | Holds the message text (business logic). |
| `view/frontend/templates/message.phtml` | HTML output; all values are escaped. |
| `etc/db_schema.xml` | Declares the `codilar_demo_submission` table. |

## Database table: `codilar_demo_submission`

| Column | Type | Notes |
|---|---|---|
| `submission_id` | int, primary key | Auto-increment |
| `name` | varchar(255) | Required |
| `email` | varchar(255) | Required |
| `message` | text | Required |
| `created_at` | timestamp | Defaults to the insert time |

## Installation

```bash
bin/magento module:enable Codilar_Demo
bin/magento setup:upgrade     # also creates the database table
bin/magento cache:flush
```

Then open `<your-store-url>/demo/index/index`.

## Not done yet / next steps

- A contact form on the page.
- A controller to validate (including email) and save submissions.
- A resource model and collection for the table.
- `etc/db_schema_whitelist.json`. Generate it with:
  `bin/magento setup:db-declaration:generate-whitelist --module-name=Codilar_Demo`
  It is needed before you change or remove columns.

## Guidelines for developers

- Follow the existing PHP 8 style: constructor promotion, `private readonly`, return types.
- Controllers implement `Http<Method>ActionInterface` instead of extending `Action`.
- Always escape output in templates.
