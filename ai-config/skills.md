# Project skills (repeatable procedures)

Short checklists for tasks that come up often in this store. Follow them in order.

## Add a new module

1. `app/code/Codilar/<Name>/registration.php` and `etc/module.xml` (add `<sequence>` for modules it depends on).
2. `bin/magento setup:upgrade`, then check `bin/magento module:status Codilar_<Name>`.

## Add a database table

1. Write `etc/db_schema.xml` (primary key, unique/index where needed).
2. `bin/magento setup:db-declaration:generate-whitelist --module-name=Codilar_<Name>`.
3. `bin/magento setup:upgrade`; verify the table and indexes in the database.
4. Seed rows with a data patch in `Setup/Patch/Data` (idempotent: use `insertOnDuplicate`).

## Add an EAV attribute (product or category)

1. Data patch implementing `DataPatchInterface`; use `EavSetupFactory->create(['setup' => $moduleDataSetup])`.
2. Wrap in `startSetup()` / `endSetup()`; choose `global` scope deliberately.
3. `setup:upgrade`, then check `eav_attribute` and the admin form.

## Add an admin menu + grid

1. `etc/acl.xml`, `etc/adminhtml/menu.xml`, `etc/adminhtml/routes.xml`.
2. Controller (`ADMIN_RESOURCE` set), layout handle `<frontName>_<controller>_<action>.xml`.
3. `ui_component/<name>_listing.xml` + `etc/di.xml` (SearchResult virtual type with `mainTable`).
4. Fulltext search needs a fulltext index in `db_schema.xml`.

## Add a Stores > Configuration setting

1. `etc/adminhtml/system.xml` (tab, section, group, field), `etc/config.xml` (default), ACL resource under `Magento_Config::config`.
2. Read it through a small class using `ScopeConfigInterface->isSetFlag/getValue`.

## Add a Hyvä storefront page

1. Frontend route (`etc/frontend/routes.xml`), controller, layout handle, phtml + ViewModel.
2. Add the module to the theme's `hyva.config.json`, run `npm run build`, flush cache.
3. Use `$escaper` for every output.

## Verify checkout end to end

1. `cd tests/e2e && npm test` (guest and logged-in specs).
2. Confirm the newest rows in `sales_order` (state `new`, status `pending`).
