(function () {
  var root = document.querySelector("[data-aov-wizard]");
  if (!root) return;
  var raw = root.getAttribute("data-kits") || "[]";
  var kits = [];
  try { kits = JSON.parse(raw); } catch (e) { kits = []; }
  var byId = {};
  kits.forEach(function (kit) { byId[kit.id] = kit; });
  var need = root.querySelector("[data-aov-need]");
  var brand = root.querySelector("[data-aov-brand]");
  var result = root.querySelector("[data-aov-wizard-result]");
  var quote = document.querySelector("#quote textarea[name='message']");
  if (!need || !result) return;

  function paint() {
    var id = need.value;
    var brandName = brand && brand.selectedOptions.length ? brand.selectedOptions[0].text : "";
    root.querySelectorAll("[data-aov-kit-card]").forEach(function (card) {
      var on = !id || id === "engineer" || card.getAttribute("data-aov-kit-card") === id;
      card.style.outline = on && id && id !== "engineer" ? "2px solid #ff6b00" : "";
    });
    if (!id) {
      result.textContent = "Pick a kit or choose an engineer visit. Supply prices show here. Labour does not.";
      return;
    }
    if (id === "engineer") {
      result.textContent = "Engineer visit for " + (root.getAttribute("data-context") || "this building")
        + (brandName && brandName !== "Not sure" ? " (" + brandName + ")" : "")
        + ". No supply price — call 07517806082 or use the form. Say whether the vent is stuck open, stuck shut, or the panel is in fault.";
    } else if (byId[id]) {
      var kit = byId[id];
      result.textContent = kit.title + " is " + kit.price + " ex VAT for supply. "
        + kit.blurb + (brandName && brandName !== "Not sure" ? " Brand you named: " + brandName + "." : "")
        + " Installation is POA.";
    }
    if (quote) {
      var line = result.textContent;
      if (quote.value.indexOf("Kit note:") === -1) {
        quote.value = (quote.value ? quote.value.replace(/\s+$/, "") + "\n" : "") + "Kit note: " + line;
      } else {
        quote.value = quote.value.replace(/Kit note:.*$/m, "Kit note: " + line);
      }
    }
  }

  need.addEventListener("change", paint);
  if (brand) brand.addEventListener("change", paint);
})();
