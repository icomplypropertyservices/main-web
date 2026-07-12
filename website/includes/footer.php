<footer class="bg-[#0a2540] text-white/90 pt-16 pb-8 mt-8">
    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-4 gap-10 text-sm">
        <div>
            <div class="flex items-center gap-3 mb-4">
                <span class="w-10 h-10 rounded-xl bg-white/10 text-white flex items-center justify-center font-bold text-sm">IC</span>
                <div class="font-semibold text-white text-lg"><?= SITE_NAME ?></div>
            </div>
            <p class="text-white/60 leading-relaxed">UK property compliance specialists covering Greater Manchester and the North West. Installation, servicing and certification you can show to insurers and landlords.</p>
            <div class="mt-4 text-xs text-white/50">17 Woodlands Park Road<br>Offerton, Stockport SK2 5DE</div>
        </div>
        <div>
            <div class="font-medium text-white mb-3">Services</div>
            <div class="space-y-1.5 text-white/70">
                <a href="/pages/services/index" class="block hover:text-white font-medium">View all services</a>
                <?php foreach (array_slice($services, 0, 6, true) as $slug => $name): ?>
                    <a href="/pages/services/<?= htmlspecialchars($slug) ?>" class="block hover:text-white"><?= htmlspecialchars($name) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div>
            <div class="font-medium text-white mb-3">Areas we cover</div>
            <div class="text-white/70 text-xs leading-relaxed">Manchester • Stockport • Bolton • Oldham • Rochdale • Wigan • Preston • Liverpool • Blackpool • Chester • Warrington and 150+ more North West towns.</div>
            <div class="mt-6">
                <div class="font-medium text-white mb-2">Contact</div>
                <a href="tel:<?= PHONE ?>" class="block text-[#ff6b00] font-semibold"><?= PHONE ?></a>
                <a href="mailto:<?= EMAIL ?>" class="block text-white/70 hover:text-white mt-1"><?= EMAIL ?></a>
            </div>
        </div>
        <div>
            <a href="https://wa.me/<?= WHATSAPP ?>" class="block bg-green-600 hover:bg-green-700 text-center py-3 rounded-2xl font-semibold mb-3">Chat on WhatsApp</a>
            <a href="/contact" class="block border border-white/30 hover:bg-white/10 text-center py-3 rounded-2xl">Request a free quote</a>
            <div class="mt-6 text-[10px] text-white/40">© <?= date('Y') ?> Icomply Property Services. All rights reserved.<br>Serving properties across the United Kingdom (North West focus).</div>
        </div>
    </div>
</footer>
<a href="https://wa.me/<?= WHATSAPP ?>?text=Hi%20Icomply%2C%20I%20need%20a%20quote%20for%20compliance%20services" target="_blank" rel="noopener"
   class="wa-fab fixed bottom-6 right-6 bg-green-600 hover:bg-green-700 text-white px-5 h-14 rounded-full flex items-center justify-center gap-2 text-sm font-semibold z-50 transition"
   aria-label="Chat on WhatsApp">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.85 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    WhatsApp
</a>
</body>
</html>
