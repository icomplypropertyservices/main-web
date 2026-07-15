# Download free stock-style images for manufacturer brands via LoremFlickr / Picsum
# Usage: .\bin\download-manufacturer-images.ps1

$ErrorActionPreference = 'Stop'

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectRoot = Split-Path -Parent $ScriptDir
$OutDir = Join-Path $ProjectRoot 'assets\images\manufacturers'

if (-not (Test-Path $OutDir)) {
    New-Item -ItemType Directory -Force -Path $OutDir | Out-Null
}

# Keyword => slug (filename without extension)
$Manufacturers = [ordered]@{
    'Kentec'                 = 'kentec'
    'Advanced fire panel'    = 'advanced-fire-panel'
    'C-Tec'                  = 'c-tec'
    'Hochiki'                = 'hochiki'
    'Apollo fire'            = 'apollo-fire'
    'Texecom'                = 'texecom'
    'Hikvision'              = 'hikvision'
    'Paxton'                 = 'paxton'
    'Videx'                  = 'videx'
    'Aiphone'                = 'aiphone'
    'Worcester Bosch'        = 'worcester-bosch'
    'Schneider electrical'   = 'schneider-electrical'
    'Hager consumer unit'    = 'hager-consumer-unit'
    'Axis camera'            = 'axis-camera'
    'Dahua CCTV'             = 'dahua-cctv'
    'Salto access'           = 'salto-access'
    'Fermax door entry'      = 'fermax-door-entry'
    'Myenergi EV charger'    = 'myenergi-ev-charger'
    'Rolec EV'               = 'rolec-ev'
}

# Search tags for LoremFlickr (comma-separated, no spaces preferred)
$Tags = @{
    'kentec'                 = 'fire,alarm,panel'
    'advanced-fire-panel'    = 'fire,alarm,control'
    'c-tec'                  = 'fire,alarm,system'
    'hochiki'                = 'smoke,detector,fire'
    'apollo-fire'            = 'fire,detector,safety'
    'texecom'                = 'security,alarm,system'
    'hikvision'              = 'cctv,security,camera'
    'paxton'                 = 'access,control,door'
    'videx'                  = 'intercom,door,entry'
    'aiphone'                = 'video,intercom,door'
    'worcester-bosch'        = 'boiler,heating,home'
    'schneider-electrical'   = 'electrical,switchgear,power'
    'hager-consumer-unit'    = 'electrical,fuse,board'
    'axis-camera'            = 'ip,camera,security'
    'dahua-cctv'             = 'cctv,surveillance,camera'
    'salto-access'           = 'smart,lock,access'
    'fermax-door-entry'      = 'door,entry,intercom'
    'myenergi-ev-charger'    = 'ev,charger,electric'
    'rolec-ev'               = 'electric,vehicle,charging'
}

function Get-ImageBytes {
    param(
        [string]$Url,
        [int]$TimeoutSec = 30
    )
    try {
        $response = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec $TimeoutSec -MaximumRedirection 5
        if ($response.StatusCode -ge 200 -and $response.StatusCode -lt 300 -and $response.Content.Length -gt 1000) {
            return $response.Content
        }
    } catch {
        Write-Warning "  Request failed: $Url - $($_.Exception.Message)"
    }
    return $null
}

function Save-Bytes {
    param(
        [byte[]]$Bytes,
        [string]$Path
    )
    [System.IO.File]::WriteAllBytes($Path, $Bytes)
}

$total = $Manufacturers.Count
$index = 0
$ok = 0
$fail = 0

Write-Host "Downloading $total manufacturer images into:" -ForegroundColor Cyan
Write-Host "  $OutDir"
Write-Host ""

foreach ($keyword in $Manufacturers.Keys) {
    $index++
    $slug = $Manufacturers[$keyword]
    $dest = Join-Path $OutDir "$slug.jpg"
    $tag  = $Tags[$slug]
    # lock=N keeps a stable image per slug across re-runs
    $lock = $index

    Write-Host "[$index/$total] $keyword -> $slug.jpg" -ForegroundColor Yellow

    $bytes = $null

    # Primary: LoremFlickr (keyword-tagged stock photos)
    # Use ${} so ? is not glued onto the variable name
    $loremUrl = "https://loremflickr.com/640/480/${tag}?lock=${lock}"
    Write-Host "  Trying LoremFlickr: $loremUrl"
    $bytes = Get-ImageBytes -Url $loremUrl

    # Fallback: Picsum (seeded random photo)
    if (-not $bytes) {
        $picsumUrl = "https://picsum.photos/seed/${slug}/640/480"
        Write-Host "  Fallback Picsum: $picsumUrl"
        $bytes = Get-ImageBytes -Url $picsumUrl
    }

    # Last resort: picsum by id
    if (-not $bytes) {
        $id = 10 + $index
        $picsumIdUrl = "https://picsum.photos/id/${id}/640/480"
        Write-Host "  Fallback Picsum id: $picsumIdUrl"
        $bytes = Get-ImageBytes -Url $picsumIdUrl
    }

    if ($bytes) {
        Save-Bytes -Bytes $bytes -Path $dest
        $sizeKb = [math]::Round($bytes.Length / 1KB, 1)
        Write-Host ("  Saved ({0} KB)" -f $sizeKb) -ForegroundColor Green
        $ok++
    } else {
        Write-Host "  FAILED - no image downloaded" -ForegroundColor Red
        $fail++
    }

    # Be polite to free image hosts
    Start-Sleep -Milliseconds 400
}

Write-Host ""
Write-Host "Done. Success: $ok  Failed: $fail  Output: $OutDir" -ForegroundColor Cyan

if ($fail -gt 0) { exit 1 } else { exit 0 }
