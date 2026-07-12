<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/seo.php';
$pageTitle = 'Contact Icomply | Free Quote North West';
$metaDesc = 'Contact Icomply in Stockport for free compliance quotes — EICR, fire alarms, gas, CCTV across Greater Manchester & North West.';
$metaKeywords = 'contact Icomply Property Services, compliance quote Stockport, EICR quote Manchester, fire alarm quote North West';
$canonicalUrl = site_url('contact.php');
require 'includes/header.php';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lead = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'service' => $_POST['service'] ?? '',
        'message' => $_POST['message'] ?? '',
        'timestamp' => date('c')
    ];
    // On Vercel the filesystem is ephemeral — write to /tmp when present, else local admin file
    $leadsPath = (getenv('VERCEL') || getenv('VERCEL_ENV'))
        ? (rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'icomply-leads.json')
        : (__DIR__ . '/admin/leads.json');
    @file_put_contents($leadsPath, json_encode($lead) . "\n", FILE_APPEND);
    $success = true;
}
?>

<section class="relative text-white py-20 overflow-hidden">
    <img src="assets/images/heroes/contact-hero.jpg" alt="Contact Icomply Property Services — UK commercial properties" class="absolute inset-0 w-full h-full object-cover" width="1600" height="700" fetchpriority="high">
    <div class="absolute inset-0 hero-overlay"></div>
    <div class="relative max-w-4xl mx-auto px-6 text-center">
        <div class="inline-flex px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-xs badge-uk mb-5">STOCKPORT · NORTH WEST UK</div>
        <h1 class="text-5xl font-extrabold tracking-tight">Contact Icomply for a free compliance quote</h1>
        <p class="mt-4 text-xl text-white/85">Stockport-based UK engineers covering Greater Manchester and the North West. Call, WhatsApp or send a form.</p>
    </div>
</section>

<section class="max-w-6xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-10">
        <div class="space-y-6">
            <div class="bg-white p-8 rounded-3xl border shadow-sm">
                <h2 class="font-bold text-2xl mb-6">Contact details</h2>
                <div class="space-y-5 text-sm">
                    <div>
                        <div class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Phone</div>
                        <a href="tel:<?= PHONE ?>" class="text-xl font-bold text-[#ff6b00]"><?= PHONE ?></a>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Email</div>
                        <a href="mailto:<?= EMAIL ?>" class="text-lg text-[#0a2540] font-medium"><?= EMAIL ?></a>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Address</div>
                        <div class="text-zinc-700"><?= ADDRESS ?></div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">WhatsApp</div>
                        <a href="https://wa.me/<?= WHATSAPP ?>" class="inline-flex mt-1 px-5 py-2.5 bg-green-600 text-white rounded-full font-semibold">Message us instantly</a>
                    </div>
                </div>
            </div>
            <div class="rounded-3xl overflow-hidden border shadow-sm">
                <img src="assets/images/heroes/uk-engineer.jpg" alt="UK compliance engineer" class="w-full h-56 object-cover" loading="lazy">
            </div>
        </div>

        <div class="bg-white p-8 md:p-10 rounded-3xl border shadow-sm">
            <?php if ($success): ?>
                <div class="p-10 bg-emerald-50 border border-emerald-200 rounded-3xl text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-2xl font-bold">OK</div>
                    <h3 class="font-bold text-xl">Thank you — request received</h3>
                    <p class="mt-2 text-emerald-700">A member of the team will contact you within 2 hours on business days.</p>
                    <a href="index.php" class="inline-block mt-6 modern-btn px-6 py-3 rounded-xl font-semibold">Back to home</a>
                </div>
            <?php else: ?>
                <h2 class="font-bold text-2xl mb-2">Request a free quote</h2>
                <p class="text-zinc-600 text-sm mb-6">Tell us the service and postcode — we will call you back.</p>
                <form method="POST" class="space-y-4" aria-label="Contact form">
                    <div>
                        <label for="contact-name" class="block text-xs font-semibold text-zinc-500 mb-1">Name</label>
                        <input type="text" id="contact-name" name="name" required class="w-full px-4 py-3 border rounded-xl">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="contact-email" class="block text-xs font-semibold text-zinc-500 mb-1">Email</label>
                            <input type="email" id="contact-email" name="email" required class="w-full px-4 py-3 border rounded-xl">
                        </div>
                        <div>
                            <label for="contact-phone" class="block text-xs font-semibold text-zinc-500 mb-1">Phone</label>
                            <input type="tel" id="contact-phone" name="phone" required class="w-full px-4 py-3 border rounded-xl">
                        </div>
                    </div>
                    <div>
                        <label for="contact-service" class="block text-xs font-semibold text-zinc-500 mb-1">Service required</label>
                        <select id="contact-service" name="service" required class="w-full px-4 py-3 border rounded-xl bg-white">
                            <option value="">Select…</option>
                            <?php foreach ($services as $s): ?><option><?= htmlspecialchars($s) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="contact-message" class="block text-xs font-semibold text-zinc-500 mb-1">Message / postcode / property details</label>
                        <textarea id="contact-message" name="message" rows="5" required class="w-full px-4 py-3 border rounded-xl"></textarea>
                    </div>
                    <button type="submit" class="w-full accent-btn py-4 rounded-2xl font-semibold">Send message</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require 'includes/footer.php'; ?>
