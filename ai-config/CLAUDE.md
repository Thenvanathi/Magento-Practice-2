# Codilar Magento Store

Magento 2.4.9 (PHP 8.3) with the Hyvä Theme 1.5.3 (Tailwind CSS v4 + Alpine.js) and the community
Hyvä React Checkout. Local URL: http://magento.local. The admin uses a custom path; read
`backend/frontName` in `app/etc/env.php`.

## Where things live

- Custom modules: `app/code/Codilar/`
  - `Employee`: `codilar_employee` table, admin menu/grid/form, Stores > Configuration toggle, storefront `/employees`
  - `CustomAttributes`: category attribute `category_tagline`, product attribute `warranty` (data patches)
  - `StoreSetup`: stores and store views (data patch)
  - `AfterPlugin`, `AroundPlugin`, `PriceValidation`: plugin examples
  - `OrderObserver`, `Demo`: observer examples
  - `PreferenceOverride`, `PrivateOverride`: preference examples
- Theme: `app/design/frontend/Codilar/hyva` (parent `Hyva/default`)
  - Tailwind sources and build: `web/tailwind` (`npm run build` writes `web/css/styles.css`)
  - Homepage: `Magento_Cms/layout/cms_index_index_id_home.xml` + `Magento_Cms/templates/home/homepage.phtml`
- End-to-end tests: `tests/e2e` (Playwright)
- Third-party code in `vendor/` is never edited.

## Commands

| Change                                         | Run                                                                                     |
| ---------------------------------------------- | --------------------------------------------------------------------------------------- |
| phtml / layout XML                             | `bin/magento cache:flush`                                                               |
| New Tailwind class                             | `cd app/design/frontend/Codilar/hyva/web/tailwind && npm run build`, then `cache:flush` |
| Module, `db_schema.xml`, data patch, `acl.xml` | `bin/magento setup:upgrade`                                                             |
| System config                                  | `bin/magento cache:clean config`                                                        |
| Production only                                | `setup:di:compile`, `setup:static-content:deploy`                                       |
| E2E checkout tests                             | `cd tests/e2e && npm test`                                                              |

If the storefront still shows old CSS, delete the stale symlink under `pub/static/frontend/Codilar/hyva/*/css/`
and reload (developer mode does not refresh it).

## Conventions

- PHP: `declare(strict_types=1)`, constructor property promotion with `readonly`, typed returns.
- Schema changes go in `etc/db_schema.xml`, then regenerate the whitelist:
  `bin/magento setup:db-declaration:generate-whitelist --module-name=<Vendor_Module>`.
- Data (rows, EAV attributes) goes in data patches. Never edit or delete an applied patch to "undo" it.
- Prefer a plugin, then an observer; use a preference only for protected/private/final cases.
- Tailwind classes used in a custom module's templates require that module's path in the theme's
  `web/tailwind/hyva.config.json` (`tailwind.include[].src`), otherwise the class is not generated.
- Templates escape output with `$escaper`; user-facing strings use `__()`.
- Match the surrounding code's comment density and naming.

## Never

- Commit or print secrets: `auth.json`, the Hyvä Private Packagist key, `app/etc/env.php` values.
- Edit files under `vendor/`, `generated/`, `var/` or `pub/static/`.
