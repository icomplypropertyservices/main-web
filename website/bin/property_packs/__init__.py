"""Shared builder for Property SEO packs (data/property-packs/{pack}.json).

Identical in every pack PR. Each pack supplies its own copy module and a
small build-{pack}-pack.py that calls property_packs.common.build_pack().
"""
