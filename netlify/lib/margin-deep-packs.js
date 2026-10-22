/**
 * Margin DEEP pack data bundled into the edge function (one import per shipped pack).
 * When packs merge, keep every import line and every entry in the list.
 */
import fencing from "../../website/data/margin-deep/fencing.json" with { type: "json" };

export const MARGIN_DEEP_PACKS = [fencing];
