# Service Images Required

Add one unique image per service in this folder with the following filenames:

- electrical.jpg
- fire-alarms.jpg
- emergency-lighting.jpg
- aov-air-handling.jpg
- nurse-call.jpg
- gas-systems.jpg
- intruder-alarm.jpg
- cctv.jpg
- access-control.jpg
- door-entry.jpg
- intercoms.jpg

**Recommended specs:**
- 1200x630px (OG image size)
- High quality, relevant to the service
- Professional photography or illustration
- Alt text should include the service name + location when used

Example usage in templates:
```php
<img src="<?= SITE_URL ?>/assets/images/<?= $serviceSlug ?>.jpg" 
     alt="<?= $serviceName ?> in <?= $area ?>" class="w-full rounded-2xl">
```