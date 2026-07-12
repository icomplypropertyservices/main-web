# Generated Pages

All files in this directory (and its subdirectories) are **auto-generated**.

## How to regenerate

From the project root:

```bash
php bin/generate-site.php --limit=150
```

- `--limit=150` → generates pages for the first 150 areas (full production)
- `--service=electrical` → only generate pages for one service

## Do not edit these files directly

Any manual changes will be lost on the next generation run.

The source of truth is:
- `templates/combo.php`
- `config.php` ($services and $areas arrays)