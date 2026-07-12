$ErrorActionPreference = 'Continue'
$base = 'http://localhost:8000'
$locs = @('manchester','stockport','bolton','liverpool','preston','blackpool','chester','warrington','oldham')
$pages = @(@{
    Label = 'MAIN nurse-call'
    Url = "$base/pages/services/nurse-call.php"
    ExpectedPath = '/pages/services/nurse-call.php'
    Location = $null
    Kind = 'main'
})
foreach ($l in $locs) {
    $titleLoc = (Get-Culture).TextInfo.ToTitleCase($l)
    $pages += @{
        Label = "COMBO $l"
        Url = "$base/pages/nurse-call/$l.php"
        ExpectedPath = "/pages/nurse-call/$l.php"
        Location = $titleLoc
        Kind = 'combo'
    }
    $pages += @{
        Label = "PRETTY $l"
        Url = "$base/nurse-call-$l"
        ExpectedPath = "/nurse-call-$l"
        Location = $titleLoc
        Kind = 'pretty'
    }
}

function Decode-Html([string]$s) {
    if (-not $s) { return $s }
    return [System.Net.WebUtility]::HtmlDecode($s)
}

function Get-MetaContent([string]$content, [string]$nameAttr) {
    $rx1 = [regex]::new("name=[`"']$nameAttr[`"']\s+content=[`"'](.*?)[`"']", 'IgnoreCase')
    $rx2 = [regex]::new("content=[`"'](.*?)[`"']\s+name=[`"']$nameAttr[`"']", 'IgnoreCase')
    $m = $rx1.Match($content)
    if ($m.Success) { return $m.Groups[1].Value }
    $m = $rx2.Match($content)
    if ($m.Success) { return $m.Groups[1].Value }
    return $null
}

function Get-PropContent([string]$content, [string]$prop) {
    $rx1 = [regex]::new("property=[`"']$([regex]::Escape($prop))[`"']\s+content=[`"'](.*?)[`"']", 'IgnoreCase')
    $rx2 = [regex]::new("content=[`"'](.*?)[`"']\s+property=[`"']$([regex]::Escape($prop))[`"']", 'IgnoreCase')
    $m = $rx1.Match($content)
    if ($m.Success) { return $m.Groups[1].Value }
    $m = $rx2.Match($content)
    if ($m.Success) { return $m.Groups[1].Value }
    return $null
}

$results = @()
foreach ($p in $pages) {
    $row = [ordered]@{
        Page = $p.Label
        Kind = $p.Kind
        Url = $p.Url
        HTTP = ''
        Title = ''
        Title_OK = ''
        Meta = ''
        Meta_OK = ''
        Canonical = ''
        Canon_OK = ''
        H1 = ''
        H1_OK = ''
        Schema = ''
        Schema_OK = ''
        Images = ''
        Images_OK = ''
        PHP_Errors = ''
        PHP_OK = ''
        Overall = ''
        Notes = @()
    }

    try {
        $resp = Invoke-WebRequest -Uri $p.Url -UseBasicParsing -TimeoutSec 20
        $html = $resp.Content
        $row.HTTP = [string]$resp.StatusCode
    } catch {
        $row.HTTP = 'ERR'
        $row.Overall = 'FAIL'
        $row.Notes = 'Fetch failed'
        $results += [pscustomobject]$row
        continue
    }

    $isHomepage = ($html -match 'Property compliance\. Done properly\.') -and ($html -match 'index\.php')

    $phpHits = [regex]::Matches($html, '(?i)(Fatal error|Parse error|Warning:|Notice:|Deprecated:|Undefined variable|Undefined index|Undefined offset|PHP Warning|PHP Notice|PHP Fatal|Stack trace:)')
    if ($phpHits.Count -gt 0) {
        $row.PHP_Errors = (($phpHits | ForEach-Object { $_.Value } | Select-Object -Unique) -join ', ')
        $row.PHP_OK = 'FAIL'
        $row.Notes += 'PHP error markers present'
    } else {
        $row.PHP_Errors = 'none'
        $row.PHP_OK = 'PASS'
    }

    if ($html -match '(?is)<title[^>]*>(.*?)</title>') {
        $title = (Decode-Html (($matches[1] -replace '\s+', ' ').Trim()))
        $row.Title = $title
        $tOk = $true
        if ([string]::IsNullOrWhiteSpace($title)) { $tOk = $false; $row.Notes += 'Empty title' }
        if ($title.Length -lt 30) { $tOk = $false; $row.Notes += "Title short ($($title.Length))" }
        if ($title.Length -gt 70) { $row.Notes += "Title long ($($title.Length) chars)" }
        if ($p.Kind -eq 'pretty' -and $isHomepage) {
            $tOk = $false
            $row.Notes += 'PRETTY URL SERVES HOMEPAGE (no rewrite/router)'
        } else {
            if ($title -notmatch '(?i)nurse\s*call') { $tOk = $false; $row.Notes += 'Title missing Nurse Call' }
            if ($p.Location -and $title -notmatch [regex]::Escape($p.Location)) {
                $tOk = $false
                $row.Notes += "Title missing $($p.Location)"
            }
        }
        $row.Title_OK = if ($tOk) { 'PASS' } else { 'FAIL' }
    } else {
        $row.Title = 'MISSING'
        $row.Title_OK = 'FAIL'
        $row.Notes += 'No title'
    }

    $desc = Get-MetaContent $html 'description'
    if ($desc) {
        $desc = (Decode-Html (($desc -replace '\s+', ' ').Trim()))
        $row.Meta = $desc
        $mOk = $true
        if ($desc.Length -lt 70) { $mOk = $false; $row.Notes += "Meta short ($($desc.Length))" }
        if ($desc.Length -gt 165) { $row.Notes += "Meta long ($($desc.Length) chars)" }
        if ($p.Kind -eq 'pretty' -and $isHomepage) {
            $mOk = $false
        } else {
            if ($desc -notmatch '(?i)nurse\s*call') { $mOk = $false; $row.Notes += 'Meta missing Nurse Call' }
            if ($p.Location -and $desc -notmatch [regex]::Escape($p.Location)) {
                $mOk = $false
                $row.Notes += "Meta missing $($p.Location)"
            }
        }
        $row.Meta_OK = if ($mOk) { 'PASS' } else { 'FAIL' }
    } else {
        $row.Meta = 'MISSING'
        $row.Meta_OK = 'FAIL'
        $row.Notes += 'No meta description'
    }

    $canon = $null
    if ($html -match 'rel=["'']canonical["'']\s+href=["''](.*?)["'']') { $canon = $matches[1] }
    elseif ($html -match 'href=["''](.*?)["'']\s+rel=["'']canonical["'']') { $canon = $matches[1] }
    if ($canon) {
        $row.Canonical = $canon
        $cOk = $true
        $reqPath = ([uri]$p.Url).AbsolutePath
        try { $canonPath = ([uri]$canon).AbsolutePath } catch { $canonPath = $canon }
        if ($p.Kind -eq 'pretty') {
            if ($isHomepage -or $canonPath -match 'index\.php') {
                $cOk = $false
                $row.Notes += "Canonical is homepage fallback ($canonPath)"
            }
        } else {
            $loc = if ($p.Location) { $p.Location.ToLower() } else { '' }
            $okPaths = @($p.ExpectedPath.TrimEnd('/'), $reqPath.TrimEnd('/'))
            if ($loc) {
                $okPaths += "/nurse-call-$loc"
                $okPaths += "/$loc/nurse-call"
            }
            if ($okPaths -notcontains $canonPath.TrimEnd('/')) {
                $cOk = $false
                $row.Notes += "Canonical not self-ref (got $canonPath, req $reqPath)"
            }
        }
        if ($canon -match 'localhost|127\.0\.0\.1') { $row.Notes += 'Canonical host=localhost (dev)' }
        $row.Canon_OK = if ($cOk) { 'PASS' } else { 'FAIL' }
    } else {
        $row.Canonical = 'MISSING'
        $row.Canon_OK = 'FAIL'
        $row.Notes += 'No canonical'
    }

    $h1matches = [regex]::Matches($html, '(?is)<h1\b[^>]*>(.*?)</h1>')
    $h1texts = @()
    foreach ($m in $h1matches) {
        $t = Decode-Html ((($m.Groups[1].Value -replace '<[^>]+>', ' ') -replace '\s+', ' ').Trim())
        $h1texts += $t
    }
    if ($h1texts.Count -eq 0) {
        $row.H1 = 'MISSING'
        $row.H1_OK = 'FAIL'
        $row.Notes += 'No H1'
    } elseif ($h1texts.Count -gt 1) {
        $row.H1 = ($h1texts -join ' || ')
        $row.H1_OK = 'FAIL'
        $row.Notes += "Multiple H1s ($($h1texts.Count))"
    } else {
        $row.H1 = $h1texts[0]
        $hOk = $true
        if ($p.Kind -eq 'pretty' -and $isHomepage) {
            $hOk = $false
            $row.Notes += 'H1 is homepage, not combo'
        } else {
            if ($h1texts[0] -notmatch '(?i)nurse\s*call') { $hOk = $false; $row.Notes += 'H1 missing Nurse Call' }
            if ($p.Location -and $h1texts[0] -notmatch [regex]::Escape($p.Location)) {
                $hOk = $false
                $row.Notes += "H1 missing $($p.Location)"
            }
        }
        $row.H1_OK = if ($hOk) { 'PASS' } else { 'FAIL' }
    }

    $schemaBlocks = [regex]::Matches($html, '(?is)<script[^>]*type=["'']application/ld\+json["''][^>]*>(.*?)</script>')
    $types = @()
    $schemaValid = $true
    if ($schemaBlocks.Count -eq 0) {
        $schemaValid = $false
        $row.Notes += 'No JSON-LD'
    } else {
        foreach ($sb in $schemaBlocks) {
            $json = $sb.Groups[1].Value.Trim()
            try {
                $obj = $json | ConvertFrom-Json -ErrorAction Stop
                $items = @()
                if ($obj.'@graph') { $items = @($obj.'@graph') }
                elseif ($obj -is [System.Array]) { $items = $obj }
                else { $items = @($obj) }
                foreach ($it in $items) {
                    $t = $it.'@type'
                    if ($t -is [System.Array]) { $types += ($t -join '+') }
                    elseif ($t) { $types += [string]$t }
                }
            } catch {
                $schemaValid = $false
                $row.Notes += 'Invalid JSON-LD'
            }
        }
    }
    $row.Schema = "n=$($schemaBlocks.Count); $($types -join ', ')"
    if (-not $schemaValid -or $schemaBlocks.Count -eq 0 -or $types.Count -eq 0) {
        $row.Schema_OK = 'FAIL'
    } else {
        $row.Schema_OK = 'PASS'
        if ($p.Kind -eq 'combo') {
            if ($types -notcontains 'Service' -and $types -notcontains 'LocalBusiness') {
                $row.Schema_OK = 'FAIL'
                $row.Notes += 'Combo missing Service/LocalBusiness schema'
            }
            $allSchema = ($schemaBlocks | ForEach-Object { $_.Groups[1].Value }) -join ' '
            if ($p.Location -and $allSchema -notmatch [regex]::Escape($p.Location)) {
                $row.Notes += 'Schema may lack location name (soft)'
            }
        }
        if ($p.Kind -eq 'pretty' -and $isHomepage) {
            $row.Schema_OK = 'FAIL'
            $row.Notes += 'Schema is homepage schema (pretty fallback)'
        }
    }

    $imgs = [regex]::Matches($html, '(?is)<img\b[^>]*>')
    $noAlt = 0
    $emptyAlt = 0
    $contentImgs = 0
    $contentNoAlt = 0
    foreach ($img in $imgs) {
        $tag = $img.Value
        $isIcon = ($tag -match 'w-10 h-10') -or ($tag -match 'width="40"')
        if (-not $isIcon) { $contentImgs++ }
        if ($tag -notmatch 'alt=') {
            $noAlt++
            if (-not $isIcon) { $contentNoAlt++ }
        } elseif ($tag -match 'alt=["'']\s*["'']') {
            $emptyAlt++
            if (-not $isIcon) { $contentNoAlt++ }
        }
    }
    $row.Images = "total=$($imgs.Count) noAlt=$noAlt emptyAlt=$emptyAlt content=$contentImgs contentBadAlt=$contentNoAlt"
    $imgOk = $true
    if ($noAlt -gt 0) { $imgOk = $false; $row.Notes += "$noAlt img missing alt attr" }
    if ($contentNoAlt -gt 0) { $imgOk = $false; $row.Notes += "$contentNoAlt content img without descriptive alt" }
    $row.Images_OK = if ($imgOk) { 'PASS' } else { 'FAIL' }
    if ($emptyAlt -gt 0 -and $contentNoAlt -eq 0) { $row.Notes += "$emptyAlt decorative empty alts OK" }

    $checks = @($row.Title_OK, $row.Meta_OK, $row.Canon_OK, $row.H1_OK, $row.Schema_OK, $row.Images_OK, $row.PHP_OK)
    $row.Overall = if ($checks -contains 'FAIL') { 'FAIL' } else { 'PASS' }
    $row.Notes = ($row.Notes -join ' | ')
    $results += [pscustomobject]$row
}

Write-Output '################ REAL PAGES (primary audit) ################'
$primary = $results | Where-Object { $_.Kind -in @('main', 'combo') }
foreach ($r in $primary) {
    Write-Output '============================================================'
    Write-Output ("PAGE: {0}  OVERALL: {1}" -f $r.Page, $r.Overall)
    Write-Output ("URL:  {0}  HTTP:{1}" -f $r.Url, $r.HTTP)
    Write-Output ("  Title     [{0}] {1}" -f $r.Title_OK, $r.Title)
    Write-Output ("  Meta      [{0}] {1}" -f $r.Meta_OK, $r.Meta)
    Write-Output ("  Canonical [{0}] {1}" -f $r.Canon_OK, $r.Canonical)
    Write-Output ("  H1        [{0}] {1}" -f $r.H1_OK, $r.H1)
    Write-Output ("  Schema    [{0}] {1}" -f $r.Schema_OK, $r.Schema)
    Write-Output ("  Images    [{0}] {1}" -f $r.Images_OK, $r.Images)
    Write-Output ("  PHP       [{0}] {1}" -f $r.PHP_OK, $r.PHP_Errors)
    if ($r.Notes) { Write-Output ("  Notes: {0}" -f $r.Notes) }
}

Write-Output ''
Write-Output 'PRIMARY SUMMARY:'
$primary | Select-Object Page, Overall, Title_OK, Meta_OK, Canon_OK, H1_OK, Schema_OK, Images_OK, PHP_OK | Format-Table -AutoSize | Out-String -Width 220 | Write-Output
$pp = @($primary | Where-Object Overall -eq 'PASS').Count
$pf = @($primary | Where-Object Overall -eq 'FAIL').Count
Write-Output ("PRIMARY PASS: {0} / {1} | FAIL: {2} / {1}" -f $pp, $primary.Count, $pf)

Write-Output ''
Write-Output '################ PRETTY URL ROUTING CHECK ################'
$pretty = $results | Where-Object Kind -eq 'pretty'
$pretty | Select-Object Page, Overall, Title_OK, Canon_OK, H1_OK, Notes | Format-Table -AutoSize | Out-String -Width 220 | Write-Output
$prettyFail = @($pretty | Where-Object { $_.Notes -match 'HOMEPAGE' }).Count
Write-Output ("Pretty URLs serving homepage: {0}/{1}" -f $prettyFail, $pretty.Count)

Write-Output ''
Write-Output '################ MANCHESTER COMBO DEEP ################'
$h = (Invoke-WebRequest -Uri 'http://localhost:8000/pages/nurse-call/manchester.php' -UseBasicParsing).Content
$ogTitle = Get-PropContent $h 'og:title'
$ogUrl = Get-PropContent $h 'og:url'
$robots = Get-MetaContent $h 'robots'
Write-Output ("OG title: {0}" -f $(if ($ogTitle) { $ogTitle } else { 'missing' }))
Write-Output ("OG url: {0}" -f $(if ($ogUrl) { $ogUrl } else { 'missing' }))
Write-Output ("robots: {0}" -f $(if ($robots) { $robots } else { 'missing' }))

$schemaBlocks = [regex]::Matches($h, '(?is)<script[^>]*type=["'']application/ld\+json["''][^>]*>(.*?)</script>')
foreach ($sb in $schemaBlocks) {
    $j = $sb.Groups[1].Value.Trim()
    Write-Output ("SCHEMA BLOCK ({0} chars):" -f $j.Length)
    Write-Output $j.Substring(0, [Math]::Min(700, $j.Length))
    Write-Output '---'
}

Write-Output 'CONTENT IMAGES:'
foreach ($img in [regex]::Matches($h, '(?is)<img\b[^>]*>')) {
    $t = $img.Value
    if ($t -notmatch 'w-10 h-10') {
        Write-Output (($t -replace '\s+', ' ').Trim())
    }
}

# Export JSON for report
$outPath = 'C:\Users\E-Store\Documents\icomplyproperty\website\bin\seo-audit-nurse-call-results.json'
$primary | ConvertTo-Json -Depth 4 | Set-Content -Path $outPath -Encoding UTF8
Write-Output ("Saved: {0}" -f $outPath)
