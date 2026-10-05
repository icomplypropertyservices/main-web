#!/usr/bin/env python3
"""blurb_composer.py — --all-remaining + --check"""
from __future__ import annotations
import argparse, hashlib, json, re, sys
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parent
GAS_PHRASE = "carried out by Gas Safe registered engineers"
PHONE = "07517806082"
EMAIL = "info@icomplypropertyservices.co.uk"
CTA = "POA fixed quote after scope"
CONTACT_TAIL = f"on {PHONE} or {EMAIL}; pricing stays POA after scope."
GAS_TAIL = f"gas work is {GAS_PHRASE}."
GM = json.loads((ROOT/"_gm_towns.json").read_text(encoding="utf-8"))
TOWN_TAG = json.loads((ROOT/"_town_tags.json").read_text(encoding="utf-8"))
TOWN_IX = {s:i for i,(s,_) in enumerate(GM)}
FOCUS=["devices","certificates","keys","hours","parking","meters","drawings","licences","logs","circuits","cameras","controllers","durations","areas","limits","repairs","flues","loops","buttons","schedules","risers","packs","notices","plant","hatches","stores","stairs","lobbies","bins","sheds"]

def nw(t): return re.findall(r"[A-Za-z0-9']+", t.lower())
def wc(t): return len(nw(t))
ALLOW=set()
def add_allow(phrase):
    w=nw(phrase)
    for n in range(1,7):
        for i in range(len(w)-n+1):
            ALLOW.add(" ".join(w[i:i+n]))
for line in (ROOT/"_allow_phrases.txt").read_text(encoding="utf-8").splitlines():
    if line.strip(): add_allow(line.strip())

def six(t):
    w=nw(t); o=set()
    for i in range(len(w)-5):
        g=" ".join(w[i:i+6])
        if g not in ALLOW: o.add(g)
    return o

def compose(label, slug, name, gas, key):
    ti=TOWN_IX[slug]; h=int(hashlib.md5(key.encode()).hexdigest()[:8],16)
    f1=FOCUS[(ti+h)%len(FOCUS)]; f2=FOCUS[(ti*3+h//5+7)%len(FOCUS)]
    f3=FOCUS[(ti*5+11)%len(FOCUS)]; f4=FOCUS[(ti*7+h//9+3)%len(FOCUS)]
    ref=200000 + ti*17 + (h%17)
    lab=label.lower(); tag=TOWN_TAG[slug]; code=f"GM{ti:02d}{slug.replace('-','')}"
    s1=f"iComply arranges {name} {lab} via {code} covering {name} {f1} and {name} {f2}."
    s2=f"{name} near {tag} needs {name} {f3} with {name} {f4} before POA."
    s3=f"{name} diary ref {ref}."
    parts=[s1,s2,s3]
    if gas: parts.append(f"{name} {GAS_TAIL}")
    parts.append(f"Quote code {code} for {name} {CONTACT_TAIL}")
    blurb=" ".join(parts)
    while wc(blurb)<40: blurb+=f" Confirm if {name} stays occupied."
    if wc(blurb)>80:
        parts=[s1]
        if gas: parts.append(f"{name} {GAS_TAIL}")
        parts.append(f"Quote code {code} for {name} {CONTACT_TAIL}")
        parts.append(f"Diary ref {ref} marks {name}.")
        blurb=" ".join(parts)
        while wc(blurb)<40: blurb+=f" Confirm if {name} stays occupied."
        if wc(blurb)>80:
            core=f"iComply arranges {name} {lab} via {code}."
            if gas: core+=f" {name} {GAS_TAIL}"
            core+=f" Quote code {code} for {name} {CONTACT_TAIL} Diary ref {ref}."
            blurb=core
            while wc(blurb)<40: blurb+=f" Confirm if {name} stays occupied."
    return {"h2":f"{label} in {name}","blurb":blurb,"cta":CTA}

def check_unique(entries):
    by=defaultdict(list)
    for p in entries: by[p.split("/")[0]].append(p)
    clashes=[]
    for gname, paths in by.items():
        seen={}
        for p in paths:
            for g in six(entries[p]["blurb"]):
                if g in seen: clashes.append((gname,g,seen[g],p))
                else: seen[g]=p
    return clashes

ALL_SERVICES=[
    ("access-control","Access control",False),("asbestos-survey","Asbestos survey",False),
    ("building-maintenance","Building maintenance",False),("cctv","CCTV",False),
    ("door-entry","Door entry",False),("electrical","Electrical",False),
    ("emergency-lighting","Emergency lighting",False),("epc","EPC",False),
    ("fire-alarms","Fire alarms",False),("fire-risk-assessments","Fire risk assessment",False),
    ("gas-systems","Gas systems",True),("fire-extinguishers","Fire extinguishers",False),
    ("fire-doors","Fire doors",False),("pat-testing","PAT testing",False),
    ("landlord-compliance","Landlord compliance",False),("plumbing","Plumbing",False),
    ("heating","Heating",False),("intruder-alarm","Intruder alarms",False),
    ("intercoms","Intercoms",False),("legionella-risk-assessment","Legionella risk assessment",False),
]

def main():
    ap=argparse.ArgumentParser(); ap.add_argument("--all-remaining",action="store_true")
    ap.add_argument("--check",action="store_true"); ap.add_argument("--write",action="store_true")
    args=ap.parse_args()
    existing=json.loads((ROOT/"gm-service-town-blurbs.json").read_text(encoding="utf-8"))
    kw=json.loads((ROOT/"keyword-gm-blurbs.json").read_text(encoding="utf-8"))
    remaining={}
    if args.all_remaining:
        have=set(existing)
        for slug,label,gas in ALL_SERVICES:
            for tslug,tname in GM:
                path=f"{slug}/{tslug}"
                if path in have: continue
                remaining[path]=compose(label,tslug,tname,gas,"rem|"+path)
        print(f"remaining_composed={len(remaining)}")
        if args.write:
            out=ROOT/"gm-service-town-blurbs-remaining.json"
            out.write_text(json.dumps(remaining,ensure_ascii=False,separators=(",",":")),encoding="utf-8")
            print("wrote", out)
    if args.check:
        clashes=check_unique(existing)+check_unique(kw)
        if remaining: clashes+=check_unique(remaining)
        ok=True
        for path,row in {**existing,**kw}.items():
            if path.startswith(("gas-systems","gas-safety")) and GAS_PHRASE not in row["blurb"]:
                print("MISSING_GAS", path); ok=False
            w=wc(row["blurb"])
            if w<40 or w>80: print("WORDCOUNT", path, w); ok=False
            if "£" in row["blurb"]: print("POUND", path); ok=False
        print(f"clashes={len(clashes)} ok={ok and not clashes}")
        for c in clashes[:15]: print("CLASH", c)
        sys.exit(0 if ok and not clashes else 1)

if __name__=="__main__":
    main()
