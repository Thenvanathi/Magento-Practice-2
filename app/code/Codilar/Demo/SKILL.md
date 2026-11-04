---
name: codilar-demo
description: Reference for the Codilar_Demo Magento module (MVC demo page at /demo/index/index plus a declared submissions table). Use when modifying or extending this module.
---

# Codilar_Demo

Path: `app/code/Codilar/Demo`. Learning/demo module showing the Magento MVC flow: Controller -> Layout XML -> Block -> Model -> Template.

## Module declaration
- Name: `Codilar_Demo` ([etc/module.xml](etc/module.xml)); `<sequence>` loads after `Magento_Store`. No `setup_version` (declarative schema is used).
- Registered in [registration.php](registration.php).

## Frontend route
- [etc/frontend/routes.xml](etc/frontend/routes.xml): router `standard`, route id `demo`, frontName `demo`.
- URL pattern: `/demo/<controller>/<action>`.

## Request flow for `/demo/index/index`
| Layer | File | Notes |
|---|---|---|
| Controller | [Controller/Index/Index.php](Controller/Index/Index.php) | Implements `HttpGetActionInterface` (GET only), returns `PageFactory->create()`. Uses constructor promotion with `private readonly`. |
| Layout | [view/frontend/layout/demo_index_index.xml](view/frontend/layout/demo_index_index.xml) | File name = `<route>_<controller>_<action>`. Adds block `codilar.demo.message` into container `content`. |
| Block | [Block/Message.php](Block/Message.php) | Extends `Template`, injects `Model\Message`, exposes `getMessage()`. |
| Model | [Model/Message.php](Model/Message.php) | Plain class (not a DB model); `getMessage()` returns a static string. |
| Template | [view/frontend/templates/message.phtml](view/frontend/templates/message.phtml) | Referenced as `Codilar_Demo::message.phtml`. Output escaped via `$escaper->escapeHtml()`. |

## Database
- [etc/db_schema.xml](etc/db_schema.xml) declares table `codilar_demo_submission` (innodb, default connection): `submission_id` (int PK, auto-increment), `name` varchar(255), `email` varchar(255), `message` text, `created_at` timestamp (default CURRENT_TIMESTAMP).
- Created on `bin/magento setup:upgrade`.
- **Not yet implemented:** no form, no save controller, no resource model/collection for this table, and no email validation (comment says "later phase"). Only the schema exists.
- No `etc/db_schema_whitelist.json` yet. Generate with `bin/magento setup:db-declaration:generate-whitelist --module-name=Codilar_Demo` before changing/removing columns.

## Conventions to follow
- PHP 8 style: constructor promotion, `private readonly`, return types.
- Controllers implement `Http<Method>ActionInterface` rather than extending `Action`.
- Comments in this module are brief, explanatory (one-liners above each element); keep that style.
- Always escape template output.

## Common commands
```
bin/magento module:enable Codilar_Demo
bin/magento setup:upgrade
bin/magento cache:flush
```
Verify at `<base_url>/demo/index/index`.
