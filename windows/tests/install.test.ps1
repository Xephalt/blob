$script = Join-Path $PSScriptRoot '..\install.ps1'
$script:failed = 0

function Check($name, [bool]$ok) {
    if ($ok) { Write-Host "ok   - $name" } else { Write-Host "FAIL - $name"; $script:failed++ }
}

function New-Sandbox {
    $root = Join-Path ([IO.Path]::GetTempPath()) ("winsetup-" + [guid]::NewGuid())
    $stubs = "$root\stubs"
    $wt = "$root\local\Packages\Microsoft.WindowsTerminal_8wekyb3d8bbwe\LocalState"
    New-Item -ItemType Directory -Force $stubs, $wt, "$root\home", "$root\docs" | Out-Null
    foreach ($tool in 'winget', 'oh-my-posh') {
        Set-Content "$stubs\$tool.cmd" "@echo $tool %* >> `"$root\calls`""
    }
    New-Item -ItemType File "$root\calls" | Out-Null
    $settings = "// genere par Windows Terminal`n" +
        '{"defaultProfile":"x","profiles":{"defaults":{},"list":[{"name":"PowerShell"}]},"schemes":[]}'
    Set-Content "$wt\settings.json" $settings
    @{ Root = $root; Wt = "$wt\settings.json" }
}

function Invoke-Install($box) {
    $saved = $env:USERPROFILE, $env:LOCALAPPDATA, $env:Path
    $env:USERPROFILE = "$($box.Root)\home"
    $env:LOCALAPPDATA = "$($box.Root)\local"
    $env:Path = "$($box.Root)\stubs;$env:Path"
    $env:SETUP_DOCS = "$($box.Root)\docs"
    try { Get-Content $script -Raw | Invoke-Expression *>&1 | Out-Null }
    finally { $env:USERPROFILE, $env:LOCALAPPDATA, $env:Path = $saved }
}

$box = New-Sandbox
Set-Content "$($box.Root)\docs\keep.txt" 'x'
New-Item -ItemType Directory -Force "$($box.Root)\docs\PowerShell" | Out-Null
Set-Content "$($box.Root)\docs\PowerShell\Microsoft.PowerShell_profile.ps1" '# ligne perso'
Invoke-Install $box

$calls = Get-Content "$($box.Root)\calls" -Raw
$theme = "$($box.Root)\home\.config\oh-my-posh\tokyonight_stormx.omp.json"
$p7 = "$($box.Root)\docs\PowerShell\Microsoft.PowerShell_profile.ps1"
$p5 = "$($box.Root)\docs\WindowsPowerShell\Microsoft.PowerShell_profile.ps1"

Check 'should install Oh My Posh with winget' ($calls -match 'winget install .*JanDeDobbeleer\.OhMyPosh')
Check 'should install the font' ($calls -match 'oh-my-posh font install CascadiaCode')
Check 'should write a valid theme' ((Get-Content $theme -Raw | ConvertFrom-Json).palette.'main-bg' -eq '#24283b')
Check 'should load the prompt in PowerShell 7' ((Get-Content $p7 -Raw) -match 'oh-my-posh init pwsh')
Check 'should load the prompt in Windows PowerShell' ((Get-Content $p5 -Raw) -match 'oh-my-posh init pwsh')
Check 'should keep existing profile lines' ((Get-Content $p7 -Raw) -match 'ligne perso')

$wt = Get-Content $box.Wt -Raw | ConvertFrom-Json
Check 'should set the font in Windows Terminal' ($wt.profiles.defaults.font.face -eq 'CaskaydiaCove Nerd Font')
Check 'should set Campbell colors' ($wt.profiles.defaults.colorScheme -eq 'Campbell')
Check 'should keep the existing terminal profiles' ($wt.profiles.list[0].name -eq 'PowerShell')
Check 'should back up Windows Terminal settings' (Test-Path "$($box.Wt).bak")

Invoke-Install $box
$count = ([regex]::Matches((Get-Content $p7 -Raw), 'oh-my-posh init pwsh')).Count
Check 'should not duplicate the profile block on rerun' ($count -eq 1)
Check 'should keep the original settings backup on rerun' ((Get-Content "$($box.Wt).bak" -Raw) -match 'genere par')

$box = New-Sandbox
Remove-Item $box.Wt
Invoke-Install $box
Check 'should still set the prompt when Windows Terminal never ran' (Test-Path "$($box.Root)\docs\PowerShell\Microsoft.PowerShell_profile.ps1")

Write-Host "---"; Write-Host "$script:failed ko"
exit $script:failed
