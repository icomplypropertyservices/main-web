# UK mainland towns with population over 10,000

Filter used for AOV and Barriers town pages. Threshold is **greater than 10,000** usual or gazetteer population.

## Sources

1. **GeoNames `cities5000`** — [download.geonames.org/export/dump/cities5000.zip](https://download.geonames.org/export/dump/cities5000.zip), country code `GB`, `population` field greater than 10,000. Admin names from `admin1CodesASCII.txt` and `admin2Codes.txt`. Feature codes kept: `PPL`, `PPLA`, `PPLA2`, `PPLA3`, `PPLA4`, `PPLC`. Dropped: `PPLX` (section of a populated place) and `PPLL` (populated locality), which are neighbourhoods rather than towns.
2. **London boroughs** — ONS Census 2021 built-up area method does not split Greater London into the same town geography; boroughs are the settlement units ([ONS towns and cities, Census 2021](https://www.ons.gov.uk/peoplepopulationandcommunity/housing/articles/townsandcitiescharacteristicsofbuiltupareasenglandandwales/census2021)). Borough names and **2022 ONS population estimates** are taken from the Wikipedia table [List of London boroughs](https://en.wikipedia.org/wiki/List_of_London_boroughs). The City of London is excluded (resident population under 10,000). Boroughs already in GeoNames are not given a second page; the borough estimate replaces the GeoNames figure.
3. **Mainland** — England, Scotland and Wales only. Excluded: Northern Ireland; Isle of Wight; Isles of Scilly; Anglesey; Shetland; Orkney; Eilean Siar / Na h-Eileanan Siar; names beginning with “Isle of”.

`website/data/areas.json` is **not** this list. It is a North West marketing list of about 168 places and includes towns under 10,000.

## Counts

- Towns: **995**
- England: 840
- Scotland: 93
- Wales: 62
- London borough rows added beyond GeoNames: 23

### By region

- South East: 164
- North West: 147
- East of England: 104
- South West: 90
- Yorkshire and the Humber: 88
- East Midlands: 80
- West Midlands: 78
- Wales: 62
- North East: 47
- West Central Scotland: 45
- London: 42
- East and Central Scotland: 18
- South East Scotland: 16
- North East Scotland: 8
- Highlands and Argyll: 4
- South Scotland: 2

## Slugs

Slugs follow the site rule in `areaSlug()`: lower case, non-alphanumeric characters become hyphens. If two towns share a name, the county (or the nation, when the county name matches the town) is appended, for example Newport in Wales and Newport in Telford and Wrekin.

## Rebuild

```bash
python3 website/bin/build-uk-mainland-towns.py \
  --geonames /tmp/towns/cities5000.txt \
  --admin1 /tmp/towns/admin1CodesASCII.txt \
  --admin2 /tmp/towns/admin2Codes.txt \
  --london-html /tmp/towns/london.html
```
