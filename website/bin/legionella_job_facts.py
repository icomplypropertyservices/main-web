"""Hand-authored Legionella / water hygiene job facts. Build-time only."""
from __future__ import annotations

from legionella_facts_a import FACTS_A
from legionella_facts_b import FACTS_B
from legionella_facts_c import FACTS_C

GROUPS = [
    {"key": "assessments", "label": "Assessments and reviews"},
    {"key": "tanks", "label": "Tanks and stored water"},
    {"key": "temperatures", "label": "Temperatures, calorifiers and TMVs"},
    {"key": "flushing", "label": "Flushing and outlets"},
    {"key": "sampling", "label": "Sampling and interpretation"},
    {"key": "schemes", "label": "Schemes, logs and dutyholder packs"},
    {"key": "premises", "label": "Premises visits"},
    {"key": "occupancy", "label": "Voids, lets and handover"},
    {"key": "plant", "label": "Plant, advice and close-out"},
]

FACTS = FACTS_A + FACTS_B + FACTS_C
