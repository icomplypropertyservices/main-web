# Download free stock images (LoremFlickr / Picsum) for Icomply services + keywords
# Free-use stock-style images for development/SEO placeholders.
# Replace with licensed commercial stock before production if required.

$ErrorActionPreference = "Continue"
$base = "C:\xampp\htdocs\icomply\assets\images"
$svcDir = Join-Path $base "services"
$kwDir  = Join-Path $base "keywords"
New-Item -ItemType Directory -Path $svcDir, $kwDir -Force | Out-Null

function Get-Slug([string]$s) {
    $s = $s.ToLower().Trim()
    $s = $s -replace '[^a-z0-9]+','-'
    $s = $s.Trim('-')
    return $s
}

function Download-Image([string]$url, [string]$outFile) {
    if (Test-Path $outFile) {
        $len = (Get-Item $outFile).Length
        if ($len -gt 5000) { return $true }
    }
    try {
        $wc = New-Object System.Net.WebClient
        $wc.Headers.Add("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) IcomplyImageBot/1.0")
        $wc.DownloadFile($url, $outFile)
        $len = (Get-Item $outFile).Length
        if ($len -lt 2000) {
            Remove-Item $outFile -Force -ErrorAction SilentlyContinue
            return $false
        }
        return $true
    } catch {
        return $false
    }
}

function Download-KeywordImage([string]$keyword, [string]$outFile, [int]$seed) {
    # Prefer LoremFlickr (keyword-relevant free stock photos)
    $tags = ($keyword -replace '\s+',',').ToLower()
    $tags = $tags -replace '[^a-z0-9,]',''
    if ([string]::IsNullOrWhiteSpace($tags)) { $tags = "construction,building" }

    $urls = @(
        "https://loremflickr.com/1200/800/$tags"
        "https://picsum.photos/seed/$seed/1200/800.jpg"
        "https://picsum.photos/id/$(($seed % 1000))/1200/800.jpg"
    )

    foreach ($u in $urls) {
        if (Download-Image $u $outFile) {
            Write-Host "  OK  $outFile"
            return $true
        }
    }
    Write-Host "  FAIL $outFile"
    return $false
}

# --- Services (thematic tags) ---
$services = @{
    "electrical"          = "electrician,electrical,wiring"
    "fire-alarms"         = "fire,alarm,safety"
    "emergency-lighting"  = "emergency,lighting,exit"
    "aov-air-handling"    = "ventilation,hvac,smoke"
    "nurse-call"          = "hospital,nurse,care"
    "gas-systems"         = "boiler,gas,heating"
    "intruder-alarm"      = "security,alarm,burglar"
    "cctv"                = "cctv,security,camera"
    "access-control"      = "access,control,door"
    "door-entry"          = "door,entry,intercom"
    "intercoms"           = "intercom,communication,building"
}

Write-Host "=== SERVICE IMAGES ==="
$i = 100
foreach ($k in $services.Keys) {
    $out = Join-Path $svcDir "$k.jpg"
    $tags = $services[$k]
    $ok = Download-Image "https://loremflickr.com/1200/800/$tags" $out
    if (-not $ok) {
        Download-KeywordImage $tags $out $i | Out-Null
    } else {
        Write-Host "  OK  $out"
    }
    $i++
}

# --- All keyword phrases (from client list) ---
$keywords = @(
# Electrical
"electrical installation","electrical maintenance","electrical services","certified electrician","electrical contractor","electrical repair","electrician near me","commercial electrician","residential electrician","electrical wiring installation","new electrical installation","lighting installation","EV charger installation","generator installation","solar panel installation","panel upgrade","smart home wiring","data cabling installation","electrical maintenance services","preventive electrical maintenance","electrical system maintenance","electrical inspection","electrical troubleshooting","circuit breaker repair","outlet repair","switch repair","electrical fault finding","power surge protection","electrical safety inspection","electrical certification","NICEIC certified","Part P certified","electrical safety certificate","EICR","PAT testing","electrical compliance certificate","landlord electrical certificate","periodic inspection report","BS 7671","24 hour emergency electrician","commercial electrical maintenance","full house rewire","landlord safety certificate","electrical installation condition report","industrial electrical services","energy efficient lighting","home electrical upgrade","kitchen electrical installation","bathroom electrical safety","solar inverter installation","battery storage electrical",
# Fire alarms
"fire alarm installation","fire alarm maintenance","fire alarm repair","fire alarm servicing","fire alarm engineer","fire alarm system","fire detection system","smoke alarm installation","heat detector installation","fire alarm testing","fire alarm inspection","fire alarm certification","fire alarm compliance","BS 5839","fire alarm panel","addressable fire alarm panel","conventional fire alarm panel","wireless fire alarm system","fire alarm upgrade","emergency lighting installation","fire safety certificate","landlord fire alarm","commercial fire alarm system","industrial fire alarm","fire alarm commissioning","fire alarm battery replacement","aspirating smoke detection","beam detection","manual call point","sounder beacon","voice alarm system","disabled refuge system","fire alarm monitoring","fire alarm engineer near me","periodic fire alarm service","fire alarm maintenance contract","Kentec fire alarm panel","Advanced fire alarm panel","Morley fire alarm panel","C-Tec fire alarm panel","Hochiki fire alarm","Apollo fire alarm","fire alarm loop tester","fire alarm design","fire alarm installation certificate",
# Emergency lighting
"emergency lighting maintenance","emergency lighting repair","emergency lighting servicing","emergency lighting engineer","emergency lighting system","emergency light fitting","emergency lighting testing","emergency lighting inspection","emergency lighting certification","emergency lighting compliance","BS 5266","emergency lighting certificate","emergency light battery replacement","emergency lighting upgrade","self testing emergency lighting","central battery emergency lighting","LED emergency lighting","non maintained emergency lighting","maintained emergency lighting","emergency exit lighting","escape route lighting","open area emergency lighting","high risk task area lighting","emergency lighting design","emergency lighting commissioning","periodic emergency lighting test","emergency lighting panel","emergency lighting inverter","automatic emergency lighting test","landlord emergency lighting","commercial emergency lighting","industrial emergency lighting","fire alarm and emergency lighting","emergency lighting near me","emergency lighting service contract",
# AOV
"AOV installation","AOV maintenance","AOV repair","AOV servicing","AOV system","automatic opening vent","smoke vent system","AOV control panel","AOV commissioning","AOV certification","BS 9991","smoke control system","natural smoke ventilation","automatic smoke vent","AOV actuator","AOV testing","AOV inspection","emergency smoke ventilation","fire rated AOV","roof AOV","window AOV","louvre AOV","AOV engineer","AOV near me","AOV maintenance contract","AOV battery backup","AOV fire alarm interface","air handling unit installation","AHU installation","air handling unit maintenance","AHU electrical control","air handling unit servicing","AHU panel","AHU inverter drive","AHU commissioning","air handling unit certification","HVAC electrical installation","air handling system repair","commercial AHU maintenance","industrial air handling unit",
# Nurse call
"nurse call system installation","nurse call system maintenance","nurse call system repair","nurse call servicing","nurse call engineer","nurse call system","nurse call panel","emergency nurse call","hospital nurse call system","care home nurse call","wireless nurse call system","wired nurse call system","nurse call pendant","pull cord nurse call","bedside nurse call","over door light nurse call","nurse call integration","nurse call testing","nurse call certification","HTM 08-03","nurse call compliance","nurse call commissioning","IP nurse call system","touch screen nurse call","staff attack alarm","emergency assistance alarm","disabled toilet alarm","wander alert system","nurse call near me","nurse call maintenance contract","hospital nurse call installation","residential care nurse call","Zettler nurse call","Ackermann nurse call","Rauland nurse call","nurse call battery backup","nurse call system upgrade",
# Gas
"gas installation","gas maintenance","gas repair","gas servicing","gas engineer","gas safety certificate","gas boiler installation","gas boiler servicing","gas boiler repair","gas pipework installation","gas pipework maintenance","LPG installation","LPG servicing","commercial gas installation","industrial gas system","gas leak detection","gas detection system","gas interlock system","gas solenoid valve","gas meter installation","gas appliance installation","gas appliance servicing","gas safety inspection","gas emergency service","gas certification","gas compliance","landlord gas safety certificate","CP44 gas safety","gas tightness testing","gas purge and commission","gas flues installation","gas flue servicing","boiler electrical control","gas fired heating system","gas heating installation","gas heating maintenance","gas system certification","emergency gas shut off","commercial gas maintenance contract","gas safety near me","gas system upgrade",
# Intruder
"intruder alarm installation","intruder alarm maintenance","intruder alarm repair","intruder alarm servicing","intruder alarm engineer","burglar alarm system","wireless intruder alarm","wired intruder alarm","intruder alarm panel","intruder alarm certification","BS 4737","alarm response","police monitored alarm","intruder alarm testing","commercial intruder alarm","residential intruder alarm","intruder alarm near me","intruder alarm maintenance contract",
# CCTV
"CCTV installation","CCTV maintenance","CCTV repair","CCTV servicing","CCTV engineer","IP CCTV system","HD CCTV camera","CCTV recording","CCTV monitoring","PTZ camera installation","CCTV certification","commercial CCTV system","wireless CCTV","video surveillance system","CCTV testing","CCTV compliance","remote CCTV access","CCTV near me",
# Access control
"access control installation","access control maintenance","access control system","door access control","proximity card reader","biometric access control","access control panel","intercom access","electric door release","access control certification","access control servicing","commercial access control","office access control system","access control near me",
# Door entry
"door entry installation","door entry maintenance","audio door entry","video door entry","door entry system","apartment door entry","intercom system","door entry servicing","door entry repair","door entry certification","multi tenant door entry","door entry engineer",
# Intercoms
"intercom installation","intercom maintenance","intercom repair","intercom servicing","intercom system","door entry intercom","audio intercom","video intercom","IP intercom","wireless intercom","apartment intercom system","multi tenant intercom","intercom engineer","intercom panel","intercom certification","intercom testing","hospital intercom","school intercom system","commercial intercom","residential intercom","intercom near me","intercom maintenance contract","video intercom installation","audio intercom repair","intercom battery backup","intercom upgrade","BT intercom system","Aiphone intercom","intercom commissioning"
)

# Dedupe
$seen = @{}
$unique = @()
foreach ($kw in $keywords) {
    $slug = Get-Slug $kw
    if (-not $seen.ContainsKey($slug)) {
        $seen[$slug] = $true
        $unique += $kw
    }
}

Write-Host ""
Write-Host "=== KEYWORD IMAGES ($($unique.Count)) ==="
$seed = 200
$okCount = 0
$failCount = 0
foreach ($kw in $unique) {
    $slug = Get-Slug $kw
    $out = Join-Path $kwDir "$slug.jpg"
    if (Download-KeywordImage $kw $out $seed) {
        $okCount++
    } else {
        $failCount++
    }
    $seed++
    Start-Sleep -Milliseconds 150
}

Write-Host ""
Write-Host "=== DONE ==="
Write-Host "Services OK: $( (Get-ChildItem $svcDir -Filter *.jpg -ErrorAction SilentlyContinue | Measure-Object).Count )"
Write-Host "Keywords OK: $okCount  Fail: $failCount"
Write-Host "Keyword folder count: $( (Get-ChildItem $kwDir -Filter *.jpg -ErrorAction SilentlyContinue | Measure-Object).Count )"
