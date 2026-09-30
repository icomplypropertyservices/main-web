/**
 * JS twin of website/bin/check-quote-builder.php (rounding + bundle).
 * Usage: node website/bin/check-quote-builder.mjs
 */
import { createRequire } from 'module';
import { readFileSync } from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const require = createRequire(import.meta.url);
const here = dirname(fileURLToPath(import.meta.url));
const root = join(here, '..');
const { calculate } = require(join(root, 'assets/js/quote-builder.js'));
const catalog = JSON.parse(readFileSync(join(root, 'data/quote-builder.json'), 'utf8'));

let fail = 0;
let pass = 0;
function say(ok, label, detail) {
  if (ok) {
    pass += 1;
    console.log('[PASS] ' + label);
  } else {
    fail += 1;
    console.log('[FAIL] ' + label + (detail ? ' — ' + detail : ''));
  }
}

const eicr10 = calculate(catalog, 10, { eicr: 1 });
say(eicr10.tierId === 'T2' && eicr10.totalPence === 224000, '10× EICR is £224 × 10 = £2,240', eicr10.totalLabel);
say(eicr10.lines[0].unitPence === 22400, 'EICR unit rounds to £224');

const bundle10 = calculate(catalog, 10, { bundle: 1 });
say(bundle10.totalPence === 585000, '10× bundle at 10% is £5,850', bundle10.totalLabel);

const trio = calculate(catalog, 10, { fra: 1, eicr: 1, gas: 1 });
say(trio.bundleAuto === true && trio.totalPence === 585000, 'FRA+EICR+gas uses the bundle', trio.totalLabel);

const stacked = calculate(catalog, 10, { bundle: 1, fra: 1, eicr: 1, gas: 1 });
say(stacked.totalPence === 585000 && stacked.bundleExplicit === true, 'bundle does not stack with separates');

const portfolio = calculate(catalog, 25, { eicr: 1, gas: 1 });
say(portfolio.tierId === 'T4' && portfolio.totalPence === 710000, '25× EICR+gas at 15% is £7,100', portfolio.totalLabel);

const withCall = calculate(catalog, 25, { eicr: 1, gas: 1, 'callout-first': 1, 'callout-extra': 2 });
say(withCall.totalPence === 710000 + 8500 + 11000, 'call-out stays £85 + £110', withCall.totalLabel);

const fra4 = calculate(catalog, 4, { fra: 1 });
say(fra4.lines[0].unitPence === 33300 && fra4.totalPence === 133200, '4× FRA rounds to £333', fra4.totalLabel);

const eicr11 = calculate(catalog, 11, { eicr: 1 });
say(eicr11.tierId === 'T3' && eicr11.lines[0].unitPence === 21800, '12.5% EICR rounds to £218', eicr11.lines[0].unitLabel);

const nurse = calculate(catalog, 2, { nurse: 1 });
say(nurse.totalPence === 39998 && nurse.savingPence === 0, 'nurse-call stays £199.99', nurse.totalLabel);

const bundle21 = calculate(catalog, 21, { bundle: 1 });
say(bundle21.lines[0].unitPence === 55300, '15% bundle rounds £552.50 to £553', bundle21.lines[0].unitLabel);

const poa = calculate(catalog, 3, { 'eicr-1bed': 1 });
say(poa.totalLabel === 'POA' && poa.totalPence === 0, 'POA adds no pounds', poa.totalLabel);

console.log('\n' + pass + ' passed, ' + fail + ' failed');
process.exit(fail > 0 ? 1 : 0);
