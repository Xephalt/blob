$Docs = if ($env:SETUP_DOCS) { $env:SETUP_DOCS } else { [Environment]::GetFolderPath('MyDocuments') }

$ErrorActionPreference = 'Stop'
$FontFace = 'CaskaydiaCove Nerd Font'
$ThemeFile = "$env:USERPROFILE\.config\oh-my-posh\tokyonight_stormx.omp.json"
$Marker = '# >>> terminal xephal >>>'

$Theme = @'
{
  "$schema": "https://raw.githubusercontent.com/JanDeDobbeleer/oh-my-posh/main/themes/schema.json",
  "console_title_template": " {{ .Folder }} :: {{if .Root}}Admin{{end}}",
  "palette": {
    "main-bg": "#24283b",
    "terminal-red": "#f7768e",
    "pistachio-green": "#9ece6a",
    "terminal-green": "#73daca",
    "terminal-yellow": "#e0af68",
    "terminal-blue": "#7aa2f7",
    "celeste-blue": "#b4f9f8",
    "light-sky-blue": "#7dcfff",
    "terminal-white": "#c0caf5",
    "white-blue": "#a9b1d6",
    "blue-bell": "#9aa5ce",
    "pastal-grey": "#cfc9c2",
    "terminal-magenta": "#bb9af7",
    "blue-black": "#565f89",
    "terminal-black": "#414868",
    "t-background": "p:main-bg"
  },
  "blocks": [
    {
      "alignment": "left",
      "segments": [
        {
          "type": "text",
          "style": "plain",
          "background": "transparent",
          "foreground": "p:terminal-blue",
          "template": "\u00b7"
        },
        {
          "type": "path",
          "style": "plain",
          "foreground": "p:terminal-magenta",
          "properties": {
            "folder_icon": "...",
            "folder_separator_icon": "/",
            "home_icon": "~",
            "max_depth": 3,
            "style": "agnoster_short"
          },
          "template": " {{ .Path }} </>"
        },
        {
          "type": "git",
          "style": "plain",
          "foreground": "p:light-sky-blue",
          "foreground_templates": [
            "{{ if or (.Working.Changed) (.Staging.Changed) }}p:terminal-red{{ end }}",
            "{{ if and (gt .Ahead 0) (gt .Behind 0)}}p:light-sky-blue {{ end }}",
            "{{ if gt .Ahead 0 }}p:terminal-blue{{ end }}",
            "{{ if gt .Behind 0 }}p:celeste-blue{{ end }}"
          ],
          "template": "({{ .HEAD}})",
          "properties": {
            "fetch_status": true,
            "branch_icon": "\ue725 "
          }
        },
        {
          "type": "status",
          "style": "plain",
          "foreground": "p:terminal-red",
          "template": " \uf00d"
        }
      ],
      "type": "prompt"
    },
    {
      "alignment": "right",
      "overflow": "hide",
      "segments": [],
      "type": "prompt"
    },
    {
      "alignment": "left",
      "newline": true,
      "segments": [
        {
          "foreground": "p:pistachio-green",
          "style": "plain",
          "template": ">",
          "type": "text"
        }
      ],
      "type": "prompt"
    }
  ],
  "secondary_prompt": {
    "background": "transparent",
    "foreground": "p:terminal-blue",
    "template": "\u279c "
  },
  "transient_prompt": {
    "background": "p:t-background",
    "foreground": "p:terminal-blue",
    "template": "\u279c "
  },
  "final_space": true,
  "version": 3,
  "terminal_background": "p:t-background"
}
'@

$ProfileBlock = @'
# >>> terminal xephal >>>
oh-my-posh init pwsh --config "$env:USERPROFILE\.config\oh-my-posh\tokyonight_stormx.omp.json" | Invoke-Expression
Import-Module PSReadLine -ErrorAction SilentlyContinue
if ($PSVersionTable.PSVersion.Major -ge 7) {
    $esc = [char]27
    Set-PSReadLineOption -PredictionSource History
    Set-PSReadLineOption -Colors @{
        Command   = "$esc[1;36m"
        Parameter = "$esc[1;33m"
        Operator  = "$esc[1;37m"
        Variable  = "$esc[1;32m"
        Number    = "$esc[1;35m"
        Member    = "$esc[1;34m"
        String    = "$esc[1;31m"
    }
}
function ll { Get-ChildItem -Force }
function touch { param($path) New-Item -ItemType File -Path $path -Force }
function mkcd { param([string]$dir) New-Item -ItemType Directory -Path $dir -Force; Set-Location $dir }
# <<< terminal xephal <<<
'@

function Write-Utf8($path, $text) {
    New-Item -ItemType Directory -Force (Split-Path $path) | Out-Null
    [IO.File]::WriteAllText($path, $text)
}

function Set-Property($object, $name, $value) {
    $object | Add-Member -NotePropertyName $name -NotePropertyValue $value -Force
}

Write-Host '==> Oh My Posh'
if (-not (Get-Command winget -ErrorAction SilentlyContinue)) {
    throw 'winget introuvable : installe "App Installer" depuis le Microsoft Store, puis relance.'
}
winget install --id JanDeDobbeleer.OhMyPosh --exact --source winget --scope user --accept-package-agreements --accept-source-agreements
$env:Path += ';' + [Environment]::GetEnvironmentVariable('Path', 'User') + ';' + [Environment]::GetEnvironmentVariable('Path', 'Machine') + ";$env:LOCALAPPDATA\Programs\oh-my-posh\bin"

Write-Host '==> Police'
oh-my-posh font install CascadiaCode

Write-Host '==> Theme'
Write-Utf8 $ThemeFile $Theme

Write-Host '==> Profils PowerShell'
foreach ($folder in 'PowerShell', 'WindowsPowerShell') {
    $path = "$Docs\$folder\Microsoft.PowerShell_profile.ps1"
    $current = if (Test-Path $path) { [IO.File]::ReadAllText($path) } else { '' }
    if (-not $current.Contains($Marker)) { Write-Utf8 $path ($current + "`n" + $ProfileBlock + "`n") }
}

Write-Host '==> Windows Terminal'
$settings = "$env:LOCALAPPDATA\Packages\Microsoft.WindowsTerminal_8wekyb3d8bbwe\LocalState\settings.json"
if (Test-Path $settings) {
    if (-not (Test-Path "$settings.bak")) { Copy-Item $settings "$settings.bak" }
    $raw = [IO.File]::ReadAllText($settings) -replace '(?m)^\s*//.*$', ''
    $json = $raw | ConvertFrom-Json
    if (-not $json.profiles.defaults) { Set-Property $json.profiles 'defaults' ([pscustomobject]@{}) }
    Set-Property $json.profiles.defaults 'colorScheme' 'Campbell'
    Set-Property $json.profiles.defaults 'font' ([pscustomobject]@{ face = $FontFace })
    Write-Utf8 $settings ($json | ConvertTo-Json -Depth 32)
} else {
    Write-Warning "Windows Terminal jamais ouvert : ouvre-le une fois, relance ce script (ou choisis la police $FontFace a la main)."
}

if ((Get-ExecutionPolicy) -in 'Restricted', 'AllSigned') {
    Write-Warning 'PowerShell bloque les profils ici : le prompt ne se chargera pas dans Windows PowerShell. Vois avec ton IT, ou utilise PowerShell 7.'
}
Write-Host 'Fini : ferme et rouvre Windows Terminal.'